<?php
class CategoriesController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $categoryModel = $this->model('Category');
        $this->view('categories/index', [
            'title' => 'Categories',
            'categories' => $categoryModel->getAll(),
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $categoryModel = $this->model('Category');
            $name = trim($_POST['category_name'] ?? '');
            if ($name !== '') {
                $categoryModel->create(['category_name' => $name]);
                $_SESSION['success'] = 'Category added successfully.';
            }
        }
        $this->redirect('categories/index');
    }

    public function update($id): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $categoryModel = $this->model('Category');
            $name = trim($_POST['category_name'] ?? '');
            if ($name !== '') {
                $categoryModel->update((int)$id, ['category_name' => $name]);
                $_SESSION['success'] = 'Category updated successfully.';
            }
        }
        $this->redirect('categories/index');
    }

    public function delete($id): void
    {
        $this->requireLogin();
        $categoryModel = $this->model('Category');
        $categoryModel->delete((int)$id);
        $_SESSION['success'] = 'Category deleted successfully.';
        $this->redirect('categories/index');
    }
}
