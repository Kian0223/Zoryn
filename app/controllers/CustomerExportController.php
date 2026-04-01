<?php
class CustomerExportController extends Controller
{
    public function sms(): void
    {
        $this->requireLogin();

        $customerModel = $this->model('Customer');
        $rows = $customerModel->getSmsExportRows();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="customer_sms_export.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['customer_code', 'full_name', 'phone', 'email', 'total_points', 'total_spent', 'visit_count', 'birthdate']);

        foreach ($rows as $row) {
            fputcsv($out, [
                $row['customer_code'] ?? '',
                $row['full_name'] ?? '',
                $row['phone'] ?? '',
                $row['email'] ?? '',
                $row['total_points'] ?? 0,
                $row['total_spent'] ?? 0,
                $row['visit_count'] ?? 0,
                $row['birthdate'] ?? '',
            ]);
        }

        fclose($out);
        exit;
    }
}
