<?php
class ReservationsController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $reservationModel = $this->model('Reservation');
        $tableModel = $this->model('DiningTable');
        $month = trim($_GET['month'] ?? date('Y-m'));
        $dateFrom = $month . '-01';
        $dateTo = date('Y-m-t', strtotime($dateFrom));

        $this->view('reservations/index', [
            'title' => 'Reservations',
            'reservations' => $reservationModel->getAll(),
            'calendar_reservations' => $reservationModel->getCalendarData($dateFrom, $dateTo),
            'calendar_month' => $month,
            'tables' => $tableModel->getAll(),
            'summary' => $reservationModel->getTodaySummary(),
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('reservations/index');
            return;
        }

        $customerName = trim($_POST['customer_name'] ?? '');
        $customerPhone = trim($_POST['customer_phone'] ?? '');
        $customerEmail = trim($_POST['customer_email'] ?? '');
        $paxCount = (int)($_POST['pax_count'] ?? 0);
        $reservationDate = trim($_POST['reservation_date'] ?? '');
        $reservationTime = trim($_POST['reservation_time'] ?? '');
        $tableId = (int)($_POST['table_id'] ?? 0);
        $status = trim($_POST['status'] ?? 'pending');
        $notes = trim($_POST['notes'] ?? '');

        if ($customerName === '' || $paxCount <= 0 || $reservationDate === '' || $reservationTime === '') {
            $_SESSION['error'] = 'Please complete the reservation form.';
            $this->redirect('reservations/index');
            return;
        }

        $reservationModel = $this->model('Reservation');
        $tableModel = $this->model('DiningTable');

        $reservationId = $reservationModel->create([
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'customer_email' => $customerEmail,
            'pax_count' => $paxCount,
            'reservation_date' => $reservationDate,
            'reservation_time' => $reservationTime,
            'table_id' => $tableId > 0 ? $tableId : null,
            'status' => $status,
            'notes' => $notes,
            'created_by' => $_SESSION['user']['id'] ?? null,
        ]);

        if (!$reservationId) {
            $_SESSION['error'] = 'Failed to save reservation.';
            $this->redirect('reservations/index');
            return;
        }

        $reservationModel->logAction($reservationId, 'created', 'Reservation created.', $_SESSION['user']['id'] ?? null);

        if ($tableId > 0 && in_array($status, ['confirmed', 'seated'], true)) {
            $tableModel->setStatus($tableId, $status === 'seated' ? 'occupied' : 'reserved');
        }

        $_SESSION['success'] = 'Reservation added successfully.';
        $this->redirect('reservations/index');
    }

    public function update($id): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('reservations/index');
            return;
        }

        $reservationId = (int)$id;
        $reservationModel = $this->model('Reservation');
        $tableModel = $this->model('DiningTable');
        $existing = $reservationModel->findById($reservationId);

        if (!$existing) {
            $_SESSION['error'] = 'Reservation not found.';
            $this->redirect('reservations/index');
            return;
        }

        $customerName = trim($_POST['customer_name'] ?? '');
        $customerPhone = trim($_POST['customer_phone'] ?? '');
        $customerEmail = trim($_POST['customer_email'] ?? '');
        $paxCount = (int)($_POST['pax_count'] ?? 0);
        $reservationDate = trim($_POST['reservation_date'] ?? '');
        $reservationTime = trim($_POST['reservation_time'] ?? '');
        $tableId = (int)($_POST['table_id'] ?? 0);
        $status = trim($_POST['status'] ?? 'pending');
        $notes = trim($_POST['notes'] ?? '');

        if ($customerName === '' || $paxCount <= 0 || $reservationDate === '' || $reservationTime === '') {
            $_SESSION['error'] = 'Please complete the reservation form.';
            $this->redirect('reservations/index');
            return;
        }

        $ok = $reservationModel->update($reservationId, [
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'customer_email' => $customerEmail,
            'pax_count' => $paxCount,
            'reservation_date' => $reservationDate,
            'reservation_time' => $reservationTime,
            'table_id' => $tableId > 0 ? $tableId : null,
            'status' => $status,
            'notes' => $notes,
        ]);

        if (!$ok) {
            $_SESSION['error'] = 'Failed to update reservation.';
            $this->redirect('reservations/index');
            return;
        }

        $reservationModel->logAction($reservationId, 'updated', 'Reservation details updated.', $_SESSION['user']['id'] ?? null);

        if (!empty($existing['table_id']) && (int)$existing['table_id'] !== $tableId) {
            $tableModel->setStatus((int)$existing['table_id'], 'available');
        }

        if ($tableId > 0) {
            if ($status === 'confirmed') $tableModel->setStatus($tableId, 'reserved');
            elseif ($status === 'seated') $tableModel->setStatus($tableId, 'occupied');
            elseif (in_array($status, ['completed','cancelled','no_show'], true)) $tableModel->setStatus($tableId, 'available');
        }

        $_SESSION['success'] = 'Reservation updated successfully.';
        $this->redirect('reservations/index');
    }

    public function updateStatus($id): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('reservations/index');
            return;
        }

        $reservationId = (int)$id;
        $status = trim($_POST['status'] ?? '');
        $reservationModel = $this->model('Reservation');
        $tableModel = $this->model('DiningTable');
        $reservation = $reservationModel->findById($reservationId);

        if (!$reservation) {
            $_SESSION['error'] = 'Reservation not found.';
            $this->redirect('reservations/index');
            return;
        }

        if (!$reservationModel->updateStatus($reservationId, $status)) {
            $_SESSION['error'] = 'Failed to update reservation status.';
            $this->redirect('reservations/index');
            return;
        }

        $reservationModel->logAction($reservationId, 'status_changed', 'Status changed to ' . $status . '.', $_SESSION['user']['id'] ?? null);

        if (!empty($reservation['table_id'])) {
            if ($status === 'confirmed') $tableModel->setStatus((int)$reservation['table_id'], 'reserved');
            elseif ($status === 'seated') $tableModel->setStatus((int)$reservation['table_id'], 'occupied');
            elseif (in_array($status, ['completed', 'cancelled', 'no_show'], true)) $tableModel->setStatus((int)$reservation['table_id'], 'available');
        }

        $_SESSION['success'] = 'Reservation status updated successfully.';
        $this->redirect('reservations/index');
    }

    public function delete($id): void
    {
        $this->requireLogin();
        $reservationId = (int)$id;
        $reservationModel = $this->model('Reservation');
        $tableModel = $this->model('DiningTable');
        $reservation = $reservationModel->findById($reservationId);

        if (!$reservation) {
            $_SESSION['error'] = 'Reservation not found.';
            $this->redirect('reservations/index');
            return;
        }

        if (!$reservationModel->delete($reservationId)) {
            $_SESSION['error'] = 'Failed to delete reservation.';
            $this->redirect('reservations/index');
            return;
        }

        if (!empty($reservation['table_id']) && in_array($reservation['status'], ['confirmed','seated'], true)) {
            $tableModel->setStatus((int)$reservation['table_id'], 'available');
        }

        $_SESSION['success'] = 'Reservation deleted successfully.';
        $this->redirect('reservations/index');
    }
}
