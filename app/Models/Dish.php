<?php

declare(strict_types=1);

namespace App\Models;

final class Dish extends BaseModel
{
    public function allVisible(?string $categorySlug = null, ?string $search = null, string $sort = 'featured'): array
    {
        $conditions = ['d.is_visible = 1', 'c.is_visible = 1'];
        $parameters = [];

        if ($categorySlug !== null && $categorySlug !== '') {
            $conditions[] = 'c.slug = :category_slug';
            $parameters['category_slug'] = $categorySlug;
        }
        if ($search !== null && $search !== '') {
            $conditions[] = '(d.name LIKE :search_name OR d.description LIKE :search_description)';
            $term = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search) . '%';
            $parameters['search_name'] = $term;
            $parameters['search_description'] = $term;
        }

        $orderBy = match ($sort) {
            'price_asc' => 'd.price ASC, d.name ASC',
            'price_desc' => 'd.price DESC, d.name ASC',
            'name' => 'd.name ASC',
            default => 'c.sort_order ASC, d.created_at DESC, d.name ASC',
        };
        $sql = $this->selectSql()
            . ' WHERE ' . implode(' AND ', $conditions)
            . ' ORDER BY ' . $orderBy;
        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function allForAdmin(?string $search = null, ?int $categoryId = null): array
    {
        $conditions = [];
        $parameters = [];
        if ($search !== null && $search !== '') {
            $conditions[] = '(d.name LIKE :search_name OR d.slug LIKE :search_slug)';
            $term = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search) . '%';
            $parameters['search_name'] = $term;
            $parameters['search_slug'] = $term;
        }
        if ($categoryId !== null) {
            $conditions[] = 'd.category_id = :category_id';
            $parameters['category_id'] = $categoryId;
        }

        $sql = $this->selectSql() . ($conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions))
            . ' ORDER BY d.updated_at DESC, d.id DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function findVisibleBySlug(string $slug): ?array
    {
        $statement = $this->db->prepare(
            $this->selectSql() . '
             WHERE d.slug = :slug AND d.is_visible = 1 AND c.is_visible = 1
             LIMIT 1'
        );
        $statement->execute(['slug' => $slug]);
        $dish = $statement->fetch();

        return $dish === false ? null : $dish;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->db->prepare(
            $this->selectSql() . ' WHERE d.id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $dish = $statement->fetch();

        return $dish === false ? null : $dish;
    }

    public function findOrderableById(int $id): ?array
    {
        $statement = $this->db->prepare(
            $this->selectSql() . '
             WHERE d.id = :id AND d.is_visible = 1 AND d.is_available = 1 AND c.is_visible = 1
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $dish = $statement->fetch();

        return $dish === false ? null : $dish;
    }

    public function findManyByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $ids),
            static fn (int $id): bool => $id > 0
        )));
        if ($ids === []) {
            return [];
        }

        $placeholders = [];
        $parameters = [];
        foreach ($ids as $index => $id) {
            $placeholder = ':dish_id_' . $index;
            $placeholders[] = $placeholder;
            $parameters[$placeholder] = $id;
        }

        $statement = $this->db->prepare(
            'SELECT d.id, d.name, d.slug, d.description, d.image, d.price,
                    d.is_visible, d.is_available, c.name AS category_name,
                    c.is_visible AS category_visible
             FROM dishes d
             INNER JOIN categories c ON c.id = d.category_id
             WHERE d.id IN (' . implode(', ', $placeholders) . ')'
        );
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM dishes WHERE slug = :slug';
        $parameters = ['slug' => $slug];
        if ($exceptId !== null) {
            $sql .= ' AND id <> :except_id';
            $parameters['except_id'] = $exceptId;
        }

        $statement = $this->db->prepare($sql . ' LIMIT 1');
        $statement->execute($parameters);

        return $statement->fetchColumn() !== false;
    }

    public function categoryExists(int $categoryId): bool
    {
        $statement = $this->db->prepare(
            'SELECT 1 FROM categories WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $categoryId]);

        return $statement->fetchColumn() !== false;
    }

    public function create(array $dish): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO dishes
                (category_id, name, slug, description, image, price, preparation_time, is_available, is_visible)
             VALUES
                (:category_id, :name, :slug, :description, :image, :price, :preparation_time, :is_available, :is_visible)'
        );
        $statement->execute($this->writeParameters($dish));

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $dish): void
    {
        $parameters = $this->writeParameters($dish);
        $parameters['id'] = $id;
        $statement = $this->db->prepare(
            'UPDATE dishes
             SET category_id = :category_id, name = :name, slug = :slug,
                 description = :description, image = :image, price = :price,
                 preparation_time = :preparation_time, is_available = :is_available,
                 is_visible = :is_visible, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $statement->execute($parameters);
    }

    public function updateFlags(int $id, ?bool $isVisible, ?bool $isAvailable): void
    {
        $fields = [];
        $parameters = ['id' => $id];
        if ($isVisible !== null) {
            $fields[] = 'is_visible = :is_visible';
            $parameters['is_visible'] = $isVisible;
        }
        if ($isAvailable !== null) {
            $fields[] = 'is_available = :is_available';
            $parameters['is_available'] = $isAvailable;
        }
        if ($fields === []) {
            return;
        }

        $statement = $this->db->prepare(
            'UPDATE dishes SET ' . implode(', ', $fields) . ', updated_at = CURRENT_TIMESTAMP WHERE id = :id'
        );
        $statement->execute($parameters);
    }

    public function delete(int $id): void
    {
        $statement = $this->db->prepare('DELETE FROM dishes WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    private function selectSql(): string
    {
        return 'SELECT d.id, d.category_id, d.name, d.slug, d.description, d.image,
                       d.price, d.preparation_time, d.is_available, d.is_visible,
                       d.created_at, d.updated_at, c.name AS category_name, c.slug AS category_slug
                FROM dishes d
                INNER JOIN categories c ON c.id = d.category_id';
    }

    private function writeParameters(array $dish): array
    {
        return [
            'category_id' => $dish['category_id'],
            'name' => $dish['name'],
            'slug' => $dish['slug'],
            'description' => $dish['description'],
            'image' => $dish['image'],
            'price' => $dish['price'],
            'preparation_time' => $dish['preparation_time'],
            'is_available' => $dish['is_available'],
            'is_visible' => $dish['is_visible'],
        ];
    }
}
