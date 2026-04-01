<?php
class KitchenController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $orderModel = $this->model('Order');

        $this->view('kitchen/index', [
            'title' => 'Kitchen Display',
            'orders' => $orderModel->getKitchenQueue(),
            'counts' => $orderModel->getCounts(),
        ]);
    }
}
