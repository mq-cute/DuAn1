<?php
use App\Core\View;

$activeVoucher = !empty($vouchers) ? $vouchers[0] : null;
$areas = $areas ?? [];
$featuredDishes = $featuredDishes ?? [];
$categories = $categories ?? [];
$combos = $combos ?? [];
$selectedCategorySlug = $selectedCategorySlug ?? null;
?>

<!-- ================= HERO BANNER SECTION ================= -->
<section class="hp-hero">
    <div class="hp-hero-overlay"></div>
    <div class="hp-hero-content">
        <div class="hp-hero-badge">
            <span class="hp-badge-dot"></span>
            <span>MICHELIN SELECTED &bull; HAUTE GASTRONOMIE</span>
        </div>
        <h1 class="hp-hero-title">
            Nghệ Thuật Ẩm Thực <em>Đương Đại</em>
        </h1>
        <p class="hp-hero-sub">
            Nơi hội tụ tinh hoa mỹ vị cùng không gian đẳng cấp. Mỗi bữa tiệc tại Foodies là một bản giao hưởng vị giác được chế tác đặc biệt.
        </p>
        <div class="hp-hero-actions">
            <a href="/bookings/new" class="hp-btn hp-btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 4H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zM16 2v4M8 2v4M3 10h18"/></svg>
                <span>ĐẶT BÀN NGAY</span>
            </a>
            <a href="/menu" class="hp-btn hp-btn-outline">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
                <span>KHÁM PHÁ THỰC ĐƠN</span>
            </a>
        </div>
    </div>
</section>

<!-- ================= QUICK BOOKING BAR ================= -->
<div class="hp-quick-booking-container">
    <form action="/bookings/new" method="GET" class="hp-booking-bar">
        <div class="hp-bar-header">
            <div class="hp-bar-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 4H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zM16 2v4M8 2v4M3 10h18"/></svg>
                <strong>Kiểm tra & Giữ chỗ ưu tiên</strong>
            </div>
            <div class="hp-bar-note">PHẢN HỒI TỐC THỜI TRONG 60 GIÂY</div>
        </div>

        <div class="hp-bar-fields">
            <div class="hp-field">
                <label for="book_date">NGÀY PHỤC VỤ</label>
                <div class="hp-input-wrap">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <input type="date" id="book_date" name="booking_date" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div class="hp-field">
                <label for="book_time">KHUNG GIỜ</label>
                <div class="hp-input-wrap">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <select id="book_time" name="booking_time">
                        <option value="11:30">11:30 - Trưa</option>
                        <option value="12:00">12:00 - Trưa</option>
                        <option value="12:30">12:30 - Trưa</option>
                        <option value="18:00">18:00 - Tối</option>
                        <option value="19:00" selected>19:00 - Dạ tiệc tối</option>
                        <option value="20:00">20:00 - Tối</option>
                    </select>
                </div>
            </div>

            <div class="hp-field">
                <label for="book_guests">SỐ THỰC KHÁCH</label>
                <div class="hp-input-wrap">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <select id="book_guests" name="party_size">
                        <option value="2" selected>2 Khách (Bàn riêng tư)</option>
                        <option value="4">4 Khách (Bàn tiêu chuẩn)</option>
                        <option value="6">6 Khách (Bàn tiệc gia đình)</option>
                        <option value="8">8 Khách (Tiệc liên hoan)</option>
                        <option value="10">10+ Khách (Phòng VIP riêng)</option>
                    </select>
                </div>
            </div>

            <div class="hp-field">
                <label for="book_area">KHU VỰC PHÒNG</label>
                <div class="hp-input-wrap">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    <select id="book_area" name="area_id">
                        <option value="">Khu vực bất kỳ</option>
                        <?php foreach ($areas as $area): ?>
                            <option value="<?= (int)$area['id'] ?>"><?= View::escape($area['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="hp-field hp-field-submit">
                <button type="submit" class="hp-btn-search">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <span>TÌM BÀN</span>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- ================= 3 CỘT TRỤ DI SẢN SECTION ================= -->
<section class="hp-section hp-pillars-section">
    <div class="hp-container">
        <div class="hp-section-header text-center">
            <span class="hp-eyebrow">TRIẾT LÝ ẨM THỰC</span>
            <h2 class="hp-section-title">Ba Cột Trụ <em>Di Sản Foodies</em></h2>
        </div>

        <div class="hp-pillars-grid">
            <div class="hp-pillar-card">
                <div class="hp-pillar-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                </div>
                <div class="hp-pillar-tag">CUNG TUYỂN NGUYÊN MẬU</div>
                <h3 class="hp-pillar-title">Nguyên Liệu Nhập Khẩu Đỉnh Cao</h3>
                <p class="hp-pillar-desc">
                    Nguồn thực phẩm tươi sống vận chuyển đường hàng không định kỳ: Bò Wagyu A5 từ tỉnh Hyogo (Kobe), nấm kim cương đen Périgord nước Pháp, và hải sản biển sâu Alaska đạt chứng nhận chuẩn xanh.
                </p>
                <a href="/menu" class="hp-pillar-link">
                    <span>Truy xuất nguồn gốc rõ ràng</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>

            <div class="hp-pillar-card">
                <div class="hp-pillar-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 13.87A8 8 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 8 8 0 0 1 18 13.87V21H6z"/><line x1="6" y1="17" x2="18" y2="17"/></svg>
                </div>
                <div class="hp-pillar-tag">NGHỆ NHÂN BẾP TRƯỞNG</div>
                <h3 class="hp-pillar-title">Bếp Trưởng Tài Hoa Quốc Tế</h3>
                <p class="hp-pillar-desc">
                    Được dẫn dắt bởi Chef de Cuisine từng tu nghiệp tại các nhà hàng 2 - 3 sao Michelin Paris & Tokyo. Mỗi món ăn là một tác phẩm hội tụ kỹ thuật Sous-vide và nghệ thuật mạ đĩa điêu luyện.
                </p>
                <a href="/menu" class="hp-pillar-link">
                    <span>Chiêm ngưỡng thực đơn Tasting</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>

            <div class="hp-pillar-card">
                <div class="hp-pillar-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </div>
                <div class="hp-pillar-tag">DISCREET HOSPITALITY</div>
                <h3 class="hp-pillar-title">Chuẩn Mực Phục Vụ 5 Sao</h3>
                <p class="hp-pillar-desc">
                    Đội ngũ quản gia ẩm thực và chuyên gia rượu vang Sommelier am hiểu sâu sắc, phục vụ chu đáo, cẩn trọng tuyệt đối sự riêng tư và cá nhân hóa sở thích vị giác của từng thực khách danh giá.
                </p>
                <a href="/bookings/new" class="hp-pillar-link">
                    <span>Dịch vụ quản gia phòng VIP</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ================= THỰC ĐƠN TRỨ DANH SECTION ================= -->
<section class="hp-section hp-menu-section" id="thuc-don">
    <div class="hp-container">
        <div class="hp-menu-header">
            <div>
                <span class="hp-eyebrow">KIỆT TÁC DẠ TIỆC</span>
                <h2 class="hp-section-title">Thực Đơn Trứ Danh <em>Foodies</em></h2>
            </div>
            <div class="hp-category-tabs">
                <a href="/?#thuc-don" class="hp-tab <?= empty($selectedCategorySlug) ? 'is-active' : '' ?>">Tất cả món</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="/?category=<?= urlencode($cat['slug']) ?>#thuc-don" class="hp-tab <?= $selectedCategorySlug === $cat['slug'] ? 'is-active' : '' ?>">
                        <?= View::escape($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="hp-dishes-grid">
            <?php 
            $displayDishes = array_slice($featuredDishes, 0, 6);
            foreach ($displayDishes as $index => $dish): 
            ?>
                <article class="hp-dish-card">
                    <div class="hp-dish-image-wrap">
                        <img src="<?= View::escape($dish['image'] ?? 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=800&q=80') ?>" 
                             alt="<?= View::escape($dish['name']) ?>" 
                             loading="lazy">
                        <div class="hp-dish-tags">
                            <?php if ($index === 0): ?>
                                <span class="hp-tag hp-tag-gold">CHEF'S SIGNATURE</span>
                                <span class="hp-tag hp-tag-dark">BÁN CHẠY #1</span>
                            <?php elseif ($index === 1): ?>
                                <span class="hp-tag hp-tag-primary">KHUYÊN DÙNG</span>
                            <?php elseif ($index === 2): ?>
                                <span class="hp-tag hp-tag-dark">BÁN CHẠY #2</span>
                            <?php endif; ?>
                        </div>
                        <div class="hp-dish-price-badge">
                            <?= View::currency($dish['price']) ?>
                        </div>
                    </div>
                    <div class="hp-dish-body">
                        <h3 class="hp-dish-name">
                            <a href="/menu/<?= View::escape($dish['slug']) ?>"><?= View::escape($dish['name']) ?></a>
                        </h3>
                        <p class="hp-dish-desc">
                            <?= View::escape($dish['description'] ?? 'Hương vị tuyệt hảo được chế biến công phu từ nguồn nguyên liệu thượng hạng tươi mới.') ?>
                        </p>
                        <div class="hp-dish-footer">
                            <form action="/cart/items" method="POST" class="hp-inline-form">
                                <input type="hidden" name="_csrf_token" value="<?= View::escape($csrfToken) ?>">
                                <input type="hidden" name="dish_id" value="<?= (int)$dish['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="hp-btn-cart">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                                    <span>THÊM VÀO GIỎ</span>
                                </button>
                            </form>
                            <a href="/bookings/new?dish_id=<?= (int)$dish['id'] ?>" class="hp-btn-book">
                                <span>ĐẶT TRƯỚC</span>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="hp-view-all-wrap">
            <a href="/menu" class="hp-btn-view-all">
                <span>XEM TOÀN BỘ TUYỆT PHẨM TRONG THỰC ĐƠN</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
        </div>
    </div>
</section>

<!-- ================= DẠ TIỆC & SETUP KHÔNG GIAN ĐỘC BẢN ================= -->
<section class="hp-section hp-setup-section" id="combos-setup">
    <div class="hp-container">
        <div class="hp-setup-wrapper">
            <div class="hp-setup-content">
                <span class="hp-eyebrow-light">ĐẶC QUYỀN THIẾT KẾ RIÊNG</span>
                <h2 class="hp-setup-title">Dạ Tiệc & Setup Không Gian Độc Bản</h2>
                <p class="hp-setup-sub">
                    Biến mỗi khoảnh khắc quan trọng thành ký ức vô giá. Đội ngũ chuyên gia tổ chức tiệc Foodies sẵn sàng cá nhân hóa 100% không gian theo từng chi tiết mong muốn của quý khách.
                </p>

                <div class="hp-setup-features">
                    <div class="hp-feature-item">
                        <div class="hp-feature-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                        </div>
                        <div>
                            <h4>Kỷ Niệm Tình Yêu</h4>
                            <p>Hoa hồng Ecuador, nến thơm & menu lãng mạn.</p>
                        </div>
                    </div>

                    <div class="hp-feature-item">
                        <div class="hp-feature-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13"/><path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"/><path d="M7.5 8a2.5 2.5 0 0 1 0-5A4.8 4.8 0 0 1 12 8a4.8 4.8 0 0 1 4.5-5 2.5 2.5 0 0 1 0 5"/></svg>
                        </div>
                        <div>
                            <h4>Sinh Nhật Sang Trọng</h4>
                            <p>Bánh kem nghệ nhân & backdrop chụp ảnh độc quyền.</p>
                        </div>
                    </div>

                    <div class="hp-feature-item">
                        <div class="hp-feature-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 21h8M12 17v4M7 4h10l-2 9H9L7 4z"/><line x1="3" y1="4" x2="21" y2="4"/></svg>
                        </div>
                        <div>
                            <h4>Tiệc Rượu Doanh Nhân</h4>
                            <p>Hầm rượu Grand Cru & Sommelier tư vấn.</p>
                        </div>
                    </div>

                    <div class="hp-feature-item">
                        <div class="hp-feature-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                        </div>
                        <div>
                            <h4>Phòng VIP Gia Đình</h4>
                            <p>Không gian cách âm tuyệt đối, ấm cúng.</p>
                        </div>
                    </div>
                </div>

                <div class="hp-setup-cta">
                    <a href="/bookings/new?type=setup" class="hp-btn hp-btn-primary">YÊU CẦU TƯ VẤN GÓI SETUP</a>
                    <span class="hp-hotline-note">Hotline VIP: <strong>(028) 3988 8888</strong></span>
                </div>
            </div>

            <!-- Mosaic Gallery bên phải -->
            <div class="hp-setup-mosaic">
                <div class="hp-mosaic-item hp-mosaic-1">
                    <img src="https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=600&q=80" alt="Bàn tiệc lãng mạn" loading="lazy">
                </div>
                <div class="hp-mosaic-item hp-mosaic-2">
                    <img src="https://images.unsplash.com/photo-1530103862676-de8c9debad1d?auto=format&fit=crop&w=600&q=80" alt="Setup tiệc sinh nhật hoa tươi" loading="lazy">
                </div>
                <div class="hp-mosaic-item hp-mosaic-3">
                    <img src="https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?auto=format&fit=crop&w=600&q=80" alt="Hầm rượu Sommelier" loading="lazy">
                </div>
                <div class="hp-mosaic-item hp-mosaic-4">
                    <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=600&q=80" alt="Không gian phòng tiệc sang trọng" loading="lazy">
                </div>
            </div>
        </div>

        <!-- Danh sách các gói Combo / Setup từ Database -->
        <?php if (!empty($combos)): ?>
            <div class="hp-combos-carousel">
                <div class="hp-combos-header">
                    <h3 class="hp-combos-title">Các Gói Combo & Setup Thịnh Hành Đã Sẵn Sàng</h3>
                    <p class="hp-combos-sub">Chọn gói thiết kế trọn gói tiết kiệm thời gian chuẩn bị</p>
                </div>
                <div class="hp-combos-grid">
                    <?php foreach ($combos as $cb): ?>
                        <div class="hp-combo-card">
                            <div class="hp-combo-img">
                                <img src="<?= View::escape($cb['image'] ?? 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=800&q=80') ?>" alt="<?= View::escape($cb['name']) ?>">
                                <span class="hp-combo-badge"><?= View::escape($cb['suitable_for'] ?? 'Gói cao cấp') ?></span>
                            </div>
                            <div class="hp-combo-info">
                                <h4><?= View::escape($cb['name']) ?></h4>
                                <p class="hp-combo-subtitle"><?= View::escape($cb['subtitle'] ?? '') ?></p>
                                <div class="hp-combo-includes">
                                    <strong>Bao gồm:</strong> <?= View::escape($cb['includes'] ?? '') ?>
                                </div>
                                <div class="hp-combo-footer">
                                    <div class="hp-combo-price">
                                        <span class="hp-price-val"><?= View::currency($cb['price']) ?></span>
                                        <?php if (!empty($cb['original_price'])): ?>
                                            <span class="hp-price-orig"><?= View::currency($cb['original_price']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="/bookings/new?combo_id=<?= (int)$cb['id'] ?>" class="hp-btn-sm-primary">ĐẶT GÓI NÀY</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ================= ĐÁNH GIÁ THỰC KHÁCH & BÁO GIỚI ================= -->
<section class="hp-section hp-reviews-section">
    <div class="hp-container">
        <div class="hp-section-header text-center">
            <span class="hp-eyebrow">UY TÍN ĐƯỢC KHẲNG ĐỊNH</span>
            <h2 class="hp-section-title">Thực Khách Thượng Lưu <em>& Báo Giới</em></h2>
        </div>

        <div class="hp-reviews-grid">
            <div class="hp-review-card">
                <div class="hp-stars">★★★★★</div>
                <blockquote class="hp-quote">
                    "Không gian phòng VIP tuyệt mỹ cho các cuộc đàm phán cấp cao. Bò Wagyu A5 mềm mọng tan ngay trong vòm họng."
                </blockquote>
                <div class="hp-reviewer">
                    <div class="hp-avatar">ĐN</div>
                    <div class="hp-reviewer-info">
                        <strong>Ông Đặng Nam Khánh</strong>
                        <span>Chủ tịch Tập đoàn Đầu tư & Địa ốc</span>
                    </div>
                </div>
            </div>

            <div class="hp-review-card">
                <div class="hp-stars">★★★★★</div>
                <blockquote class="hp-quote">
                    "Dịch vụ setup tiệc kỷ niệm 10 năm ngày cưới của Foodies vượt xa kỳ vọng. Mọi chi tiết cắm hoa, âm nhạc đều hoàn hảo."
                </blockquote>
                <div class="hp-reviewer">
                    <div class="hp-avatar">TH</div>
                    <div class="hp-reviewer-info">
                        <strong>Bà Trần Thảo Hương</strong>
                        <span>Nhà sưu tầm nghệ thuật & Triển lãm</span>
                    </div>
                </div>
            </div>

            <div class="hp-review-card">
                <div class="hp-stars">★★★★★</div>
                <blockquote class="hp-quote">
                    "Bộ sưu tập vang Pháp và kỹ năng ghép rượu của Sommelier tại đây thuộc hàng hiếm có tại Đông Nam Á."
                </blockquote>
                <div class="hp-reviewer">
                    <div class="hp-avatar">JL</div>
                    <div class="hp-reviewer-info">
                        <strong>Jean-Luc Moreau</strong>
                        <span>Thành viên Ban Giám khảo Gastronomy Award</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dải thương hiệu báo giới công nhận -->
        <div class="hp-press-strip">
            <span class="hp-press-label">VINH DANH BỞI:</span>
            <span class="hp-press-item">&starf; MICHELIN GUIDE 2024</span>
            <span class="hp-press-item">Robb Report</span>
            <span class="hp-press-item">ELLE Gourmet</span>
            <span class="hp-press-item">Tatler Dining</span>
        </div>
    </div>
</section>

<!-- ================= BANNER VOUCHER ƯU ĐÃI ================= -->
<section class="hp-section hp-voucher-section">
    <div class="hp-container">
        <div class="hp-voucher-banner">
            <div class="hp-voucher-badge">ƯU ĐÃI ĐẶC QUYỀN HỘI VIÊN</div>
            <h2 class="hp-voucher-title">
                <?= View::escape($activeVoucher['title'] ?? 'Tặng Voucher Ưu Đãi 15% Cho Lần Đặt Bàn Đầu Tiên') ?>
            </h2>
            <p class="hp-voucher-desc">
                <?= View::escape($activeVoucher['description'] ?? 'Nhập mã đặc quyền khi đặt chỗ trực tuyến qua website để nhận ưu đãi khấu trừ trực tiếp trên tổng hóa đơn ẩm thực và miễn phí khai vị Champagne cao cấp.') ?>
            </p>

            <div class="hp-voucher-code-wrap">
                <div class="hp-code-box">
                    <span class="hp-code-label">MÃ ĐẶC QUYỀN TRỰC TUYẾN:</span>
                    <strong class="hp-code-value"><?= View::escape($activeVoucher['code'] ?? 'FOODIESFIRST15') ?></strong>
                </div>
                <span class="hp-code-condition">
                    Áp dụng cho hóa đơn từ 2.000.000 ₫ • Hạn dùng đến 31/12/2026
                </span>
            </div>

            <div class="hp-voucher-actions">
                <a href="/bookings/new?voucher=<?= urlencode($activeVoucher['code'] ?? 'FOODIESFIRST15') ?>" class="hp-btn-voucher">
                    ÁP DỤNG MÃ & ĐẶT BÀN NGAY
                </a>
                <a href="/bookings/new" class="hp-voucher-sublink">
                    <span>XEM TẤT CẢ VOUCHER THÀNH VIÊN VIP</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>
