<?php

declare(strict_types=1);

namespace App\Models;

final class Category extends BaseModel
{
    public function allVisible(): array
    {
        $statement = $this->db->query(
            'SELECT c.id, c.name, c.slug, c.description, c.image, c.sort_order,
                    COUNT(d.id) AS dish_count
             FROM categories c
             LEFT JOIN dishes d ON d.category_id = c.id AND d.is_visible = 1
             WHERE c.is_visible = 1
             GROUP BY c.id, c.name, c.slug, c.description, c.image, c.sort_order
             ORDER BY c.sort_order ASC, c.name ASC'
        );

        return $statement->fetchAll();
    }

    public function allForAdmin(): array
    {
        $statement = $this->db->query(
            'SELECT c.id, c.name, c.slug, c.description, c.image, c.sort_order, c.is_visible,
                    COUNT(d.id) AS dish_count
             FROM categories c
             LEFT JOIN dishes d ON d.category_id = c.id
             GROUP BY c.id, c.name, c.slug, c.description, c.image, c.sort_order, c.is_visible
             ORDER BY c.sort_order ASC, c.name ASC'
        );

        return $statement->fetchAll();
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, name, slug, description, image, sort_order
             FROM categories
             WHERE slug = :slug AND is_visible = 1
             LIMIT 1'
        );
        $statement->execute(['slug' => $slug]);
        $category = $statement->fetch();

        return $category === false ? null : $category;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, name, slug, description, image, sort_order, is_visible
             FROM categories WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $category = $statement->fetch();

        return $category === false ? null : $category;
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM categories WHERE slug = :slug';
        $parameters = ['slug' => $slug];
        if ($exceptId !== null) {
            $sql .= ' AND id <> :except_id';
            $parameters['except_id'] = $exceptId;
        }

        $statement = $this->db->prepare($sql . ' LIMIT 1');
        $statement->execute($parameters);

        return $statement->fetchColumn() !== false;
    }

    public function create(array $category): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO categories (name, slug, description, sort_order, is_visible)
             VALUES (:name, :slug, :description, :sort_order, :is_visible)'
        );
        $statement->execute([
            'name' => $category['name'],
            'slug' => $category['slug'],
            'description' => $category['description'],
            'sort_order' => $category['sort_order'],
            'is_visible' => $category['is_visible'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $category): void
    {
        $statement = $this->db->prepare(
            'UPDATE categories
             SET name = :name, slug = :slug, description = :description,
                 sort_order = :sort_order, is_visible = :is_visible
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'name' => $category['name'],
            'slug' => $category['slug'],
            'description' => $category['description'],
            'sort_order' => $category['sort_order'],
            'is_visible' => $category['is_visible'],
        ]);
    }

    public function updateVisibility(int $id, bool $isVisible): void
    {
        $statement = $this->db->prepare(
            'UPDATE categories SET is_visible = :is_visible WHERE id = :id'
        );
        $statement->execute(['id' => $id, 'is_visible' => $isVisible]);
    }

    public function delete(int $id): void
    {
        $statement = $this->db->prepare('DELETE FROM categories WHERE id = :id');
        $statement->execute(['id' => $id]);
    }
}
