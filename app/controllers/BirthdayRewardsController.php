<?php
class BirthdayRewardsController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $customerModel = $this->model('Customer');

        $this->view('birthday_rewards/index', [
            'title' => 'Birthday Rewards',
            'birthday_customers' => $customerModel->getBirthdayCustomersThisMonth(),
        ]);
    }
}
