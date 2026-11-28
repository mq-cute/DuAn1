<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Invoice;
use PDO;

final class InvoicePdfService
{
    /**
     * Tạo hoặc lấy đường dẫn file PDF hóa đơn và trả về nội dung nhị phân PDF.
     */
    public static function generate(array $invoice, bool $forceRegenerate = false): string
    {
        $invoiceNumber = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($invoice['invoice_number'] ?? 'INV-' . time()));
        
        // Ưu tiên lưu trong public/assets/invoices
        $storageDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'invoices';
        if (!is_dir($storageDir)) {
            @mkdir($storageDir, 0775, true);
        }

        $pdfFilePath = $storageDir . DIRECTORY_SEPARATOR . 'invoice_' . $invoiceNumber . '.pdf';
        $relativePdfPath = '/assets/invoices/invoice_' . $invoiceNumber . '.pdf';

        if (!$forceRegenerate && is_file($pdfFilePath) && filesize($pdfFilePath) > 0) {
            return (string)file_get_contents($pdfFilePath);
        }

        $pdfContent = self::buildPdfDocument($invoice);
        @file_put_contents($pdfFilePath, $pdfContent);

        // Cập nhật pdf_path vào cơ sở dữ liệu nếu có ID
        if (!empty($invoice['id'])) {
            try {
                $db = Database::connection();
                $stmt = $db->prepare('UPDATE invoices SET pdf_path = :pdf_path WHERE id = :id');
                $stmt->execute(['pdf_path' => $relativePdfPath, 'id' => (int)$invoice['id']]);
            } catch (\Throwable) {
                // Bỏ qua nếu DB bận hoặc test môi trường không có DB
            }
        }

        return $pdfContent;
    }

    /**
     * Xây dựng nội dung file PDF 1.4 tiêu chuẩn ISO 32000-1
     */
    private static function buildPdfDocument(array $invoice): string
    {
        $invNum = (string)($invoice['invoice_number'] ?? 'INV-0001');
        $customerName = self::normalizeText((string)($invoice['customer_name'] ?? 'Khach hang vang lai'));
        $issuedAt = date('d/m/Y H:i', strtotime((string)($invoice['issued_at'] ?? 'now')));
        $paymentMethod = (string)($invoice['payment_method'] ?? 'cash');
        $status = strtoupper((string)($invoice['status'] ?? 'PAID'));
        
        $methodLabels = [
            'cash' => 'Tien mat (Cash)',
            'card' => 'The tin dung / POS (Card)',
            'transfer' => 'Chuyen khoan VietQR (Transfer)',
            'ewallet' => 'Cong VNPAY / Vi dien tu (E-Wallet)'
        ];
        $methodName = $methodLabels[$paymentMethod] ?? $paymentMethod;

        $items = $invoice['items'] ?? [];
        $subtotal = (float)($invoice['subtotal'] ?? 0);
        $tax = (float)($invoice['tax'] ?? 0);
        $serviceCharge = (float)($invoice['service_charge'] ?? 0);
        $discount = (float)($invoice['discount'] ?? 0);
        $total = (float)($invoice['total'] ?? 0);
        $depositReceived = !empty($invoice['booking_deposit_received_at'])
            ? (float)($invoice['booking_deposit_amount'] ?? 0)
            : 0.0;
        $amountDue = max(0.0, $total - $depositReceived);

        // Khởi tạo các đối tượng vẽ PDF
        $commands = [];

        // 1. Header Background banner (Navy)
        $commands[] = "0.07 0.09 0.13 rg"; // #121820
        $commands[] = "40 730 515 80 re f";

        // 2. Gold line under header
        $commands[] = "0.77 0.63 0.35 RG 3 w"; // #c5a059
        $commands[] = "40 730 m 555 730 l S";

        // 3. Header Text
        $commands[] = "BT";
        $commands[] = "1 1 1 rg"; // White
        $commands[] = "/F2 18 Tf 55 780 Td (FOODIES HAUTE CUISINE) Tj";
        $commands[] = "/F1 9 Tf 0 -16 Td (Haute Cuisine Francaise & Grand Cru Wine Cellar) Tj";
        $commands[] = "0 -13 Td (Dia chi: 18 Le Duan, Ben Nghe, Quan 1, TP. Ho Chi Minh | Hotline: (+84) 28 3822 8899) Tj";
        $commands[] = "ET";

        // 4. Invoice Title & Metadata Box
        $commands[] = "BT";
        $commands[] = "0.07 0.09 0.13 rg";
        $commands[] = "/F2 16 Tf 40 695 Td (HOA DON GIA TRI GIA TANG / VAT INVOICE) Tj";
        $commands[] = "/F1 10 Tf";
        $commands[] = "0 -22 Td (So hoa don: #" . self::escapePdf($invNum) . ") Tj";
        $commands[] = "0 -16 Td (Ngay phat hanh: " . self::escapePdf($issuedAt) . ") Tj";
        $commands[] = "0 -16 Td (Khach hang: " . self::escapePdf($customerName) . ") Tj";
        $commands[] = "0 -16 Td (Phuong thuc: " . self::escapePdf($methodName) . ") Tj";
        $commands[] = "0 -16 Td (Trang thai: " . self::escapePdf($status) . ") Tj";
        $commands[] = "ET";

        // 5. Line separator
        $commands[] = "0.85 0.85 0.85 RG 1 w";
        $commands[] = "40 590 m 555 590 l S";

        // 6. Table Header
        $commands[] = "0.95 0.95 0.96 rg"; // Light gray
        $commands[] = "40 565 515 22 re f";
        $commands[] = "0.77 0.63 0.35 RG 1 w";
        $commands[] = "40 565 m 555 565 l S";

        $commands[] = "BT";
        $commands[] = "0.07 0.09 0.13 rg";
        $commands[] = "/F2 9 Tf";
        $commands[] = "50 572 Td (STT) Tj";
        $commands[] = "40 0 Td (TEN MON AN / DICH VU) Tj";
        $commands[] = "230 0 Td (SL) Tj";
        $commands[] = "50 0 Td (DON GIA) Tj";
        $commands[] = "80 0 Td (THANH TIEN) Tj";
        $commands[] = "ET";

        // 7. Table Rows
        $y = 545;
        $idx = 1;
        foreach ($items as $item) {
            $dishName = self::normalizeText((string)($item['dish_name'] ?? 'Mon an'));
            $qty = (int)($item['quantity'] ?? 1);
            $price = number_format((float)($item['unit_price'] ?? 0), 0, ',', '.') . ' d';
            $sub = number_format((float)($item['subtotal'] ?? 0), 0, ',', '.') . ' d';

            $commands[] = "BT";
            $commands[] = "0.15 0.15 0.15 rg";
            $commands[] = "/F1 9 Tf";
            $commands[] = "52 $y Td (" . $idx . ") Tj";
            $commands[] = "38 0 Td (" . self::escapePdf(substr($dishName, 0, 42)) . ") Tj";
            $commands[] = "232 0 Td (" . $qty . ") Tj";
            $commands[] = "42 0 Td (" . self::escapePdf($price) . ") Tj";
            $commands[] = "75 0 Td (" . self::escapePdf($sub) . ") Tj";
            $commands[] = "ET";

            // Row bottom line
            $commands[] = "0.92 0.92 0.92 RG 0.5 w";
            $commands[] = "40 " . ($y - 5) . " m 555 " . ($y - 5) . " l S";

            $y -= 20;
            $idx++;
            if ($idx > 15) {
                break; // A4 1-page compact layout
            }
        }

        // 8. Financial Summary Box
        $summaryY = max($y - 15, 260);
        $commands[] = "0.77 0.63 0.35 RG 1 w";
        $commands[] = "330 " . ($summaryY + 5) . " m 555 " . ($summaryY + 5) . " l S";

        $commands[] = "BT";
        $commands[] = "0.2 0.2 0.2 rg";
        $commands[] = "/F1 9 Tf";
        $commands[] = "340 $summaryY Td (Tien thuc don:) Tj 120 0 Td (" . self::escapePdf(number_format($subtotal, 0, ',', '.') . ' d') . ") Tj";
        $commands[] = "-120 -15 Td (Giam gia / Voucher:) Tj 120 0 Td (-" . self::escapePdf(number_format($discount, 0, ',', '.') . ' d') . ") Tj";
        $commands[] = "-120 -15 Td (Phi phuc vu (5%):) Tj 120 0 Td (" . self::escapePdf(number_format($serviceCharge, 0, ',', '.') . ' d') . ") Tj";
        $commands[] = "-120 -15 Td (Thue VAT (8%):) Tj 120 0 Td (" . self::escapePdf(number_format($tax, 0, ',', '.') . ' d') . ") Tj";
        if ($depositReceived > 0) {
            $commands[] = "-120 -15 Td (Tien coc da nhan:) Tj 120 0 Td (-" . self::escapePdf(number_format($depositReceived, 0, ',', '.') . ' d') . ") Tj";
        }

        // Grand Total Row (Navy + Gold)
        $commands[] = "0.07 0.09 0.13 rg";
        $commands[] = "/F2 11 Tf";
        $commands[] = "-120 -20 Td (TONG THANH TOAN:) Tj";
        $commands[] = "0.65 0.50 0.20 rg"; // Gold color
        $commands[] = "120 0 Td (" . self::escapePdf(number_format($total, 0, ',', '.') . ' VND') . ") Tj";
        if ($depositReceived > 0) {
            $commands[] = "-120 -15 Td (CON PHAI TRA:) Tj 120 0 Td (" . self::escapePdf(number_format($amountDue, 0, ',', '.') . ' VND') . ") Tj";
        }
        $commands[] = "ET";

        // 9. Footer & Signatures
        $footerY = 140;
        $commands[] = "0.85 0.85 0.85 RG 0.5 w";
        $commands[] = "40 $footerY m 555 $footerY l S";

        $commands[] = "BT";
        $commands[] = "0.3 0.3 0.3 rg";
        $commands[] = "/F1 9 Tf";
        $commands[] = "80 " . ($footerY - 20) . " Td (KHACH HANG) Tj";
        $commands[] = "330 0 Td (DAI DIEN NHA HANG) Tj";
        $commands[] = "-340 -12 Td ((Ky va ghi ro ho ten)) Tj";
        $commands[] = "330 0 Td ((Ky, dong dau va xac nhan)) Tj";

        $commands[] = "/F1 8 Tf 0.5 0.5 0.5 rg";
        $commands[] = "-250 -70 Td (Cam on Quy khach da lua chon FOODIES Haute Cuisine. Hen gap lai Quy khach!) Tj";
        $commands[] = "0 -11 Td (He thong hoa don dien tu truc tuyen theo Nghi dinh 123/2020/ND-CP - FDS e-Invoice Platform) Tj";
        $commands[] = "ET";

        $streamContent = implode("\n", $commands);
        $streamLength = strlen($streamContent);

        // Build valid PDF objects
        $pdf = "%PDF-1.4\n";
        $pdf .= "%\xE2\xE3\xCF\xD3\n";

        $offsets = [];
        $offsets[1] = strlen($pdf);
        $pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";

        $offsets[2] = strlen($pdf);
        $pdf .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";

        $offsets[3] = strlen($pdf);
        $pdf .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595.28 841.89] /Contents 4 0 R /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> >>\nendobj\n";

        $offsets[4] = strlen($pdf);
        $pdf .= "4 0 obj\n<< /Length $streamLength >>\nstream\n" . $streamContent . "\nendstream\nendobj\n";

        $offsets[5] = strlen($pdf);
        $pdf .= "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>\nendobj\n";

        $offsets[6] = strlen($pdf);
        $pdf .= "6 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>\nendobj\n";

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n";
        $pdf .= "0 7\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= 6; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer\n";
        $pdf .= "<< /Size 7 /Root 1 0 R >>\n";
        $pdf .= "startxref\n";
        $pdf .= "$xrefOffset\n";
        $pdf .= "%%EOF\n";

        return $pdf;
    }

    /**
     * Escape special PDF string characters
     */
    private static function escapePdf(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    /**
     * Transliterate Vietnamese unicode characters into legible, crisp print characters
     */
    private static function normalizeText(string $str): string
    {
        $utf8Map = [
            'à'=>'a', 'á'=>'a', 'ả'=>'a', 'ã'=>'a', 'ạ'=>'a',
            'ă'=>'a', 'ằ'=>'a', 'ắ'=>'a', 'ẳ'=>'a', 'ẵ'=>'a', 'ặ'=>'a',
            'â'=>'a', 'ầ'=>'a', 'ấ'=>'a', 'ẩ'=>'a', 'ẫ'=>'a', 'ậ'=>'a',
            'è'=>'e', 'é'=>'e', 'ẻ'=>'e', 'ẽ'=>'e', 'ẹ'=>'e',
            'ê'=>'e', 'ề'=>'e', 'ế'=>'e', 'ể'=>'e', 'ễ'=>'e', 'ệ'=>'e',
            'ì'=>'i', 'í'=>'i', 'ỉ'=>'i', 'ĩ'=>'i', 'ị'=>'i',
            'ò'=>'o', 'ó'=>'o', 'ỏ'=>'o', 'õ'=>'o', 'ọ'=>'o',
            'ô'=>'o', 'ồ'=>'o', 'ố'=>'o', 'ổ'=>'o', 'ỗ'=>'o', 'ộ'=>'o',
            'ơ'=>'o', 'ờ'=>'o', 'ớ'=>'o', 'ở'=>'o', 'ỡ'=>'o', 'ợ'=>'o',
            'ù'=>'u', 'ú'=>'u', 'ủ'=>'u', 'ũ'=>'u', 'ụ'=>'u',
            'ư'=>'u', 'ừ'=>'u', 'ứ'=>'u', 'ử'=>'u', 'ữ'=>'u', 'ự'=>'u',
            'ỳ'=>'y', 'ý'=>'y', 'ỷ'=>'y', 'ỹ'=>'y', 'ỵ'=>'y',
            'đ'=>'d',
            'À'=>'A', 'Á'=>'A', 'Ả'=>'A', 'Ã'=>'A', 'Ạ'=>'A',
            'Ă'=>'A', 'Ằ'=>'A', 'Ắ'=>'A', 'Ẳ'=>'A', 'Ẵ'=>'A', 'Ặ'=>'A',
            'Â'=>'A', 'Ầ'=>'A', 'Ấ'=>'A', 'Ẩ'=>'A', 'Ẫ'=>'A', 'Ậ'=>'A',
            'È'=>'E', 'É'=>'E', 'Ẻ'=>'E', 'Ẽ'=>'E', 'Ẹ'=>'E',
            'Ê'=>'E', 'Ề'=>'E', 'Ế'=>'E', 'Ể'=>'E', 'Ễ'=>'E', 'Ệ'=>'E',
            'Ì'=>'I', 'Í'=>'I', 'Ỉ'=>'I', 'Ĩ'=>'I', 'Ị'=>'I',
            'Ò'=>'O', 'Ó'=>'O', 'Ỏ'=>'O', 'Õ'=>'O', 'Ọ'=>'O',
            'Ô'=>'O', 'Ồ'=>'O', 'Ố'=>'O', 'Ổ'=>'O', 'Ỗ'=>'O', 'Ộ'=>'O',
            'Ơ'=>'O', 'Ờ'=>'O', 'Ớ'=>'O', 'Ở'=>'O', 'Ỡ'=>'O', 'Ợ'=>'O',
            'Ù'=>'U', 'Ú'=>'U', 'Ủ'=>'U', 'Ũ'=>'U', 'Ụ'=>'U',
            'Ư'=>'U', 'Ừ'=>'U', 'Ứ'=>'U', 'Ử'=>'U', 'Ữ'=>'U', 'Ự'=>'U',
            'Ỳ'=>'Y', 'Ý'=>'Y', 'Ỷ'=>'Y', 'Ỹ'=>'Y', 'Ỵ'=>'Y',
            'Đ'=>'D',
        ];

        return strtr($str, $utf8Map);
    }
}
