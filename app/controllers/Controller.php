<?php

class Controller
{
    private $product_model;
    private $order_model; // ta senare in som parameter i constr
    private $customer_model;
    private $view;
    private $routes;

    /**
     *
     */
    public function __construct($product_model, $customer_model, $view, $routes)
    {
        $this->product_model = $product_model;
        $this->customer_model = $customer_model;
        $this->view = $view;
        $this->routes = $routes;
        $this->resolveRoute();
    }
    //CONTENT:
    //COMMON MAIN METHODS:
    //COMMON HELPER METHODS:
    //CUSTOMER MAIN METHODS:

    private function customerRegister()
    {
        $customer_data = array();
        $errors = array();
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            try {
                $customer_data = $this->handleCustomerPost();
                $this->customer_model->createCustomer($customer_data);
                header("Location: products");
                exit();
            } catch (Exception $error) {
                $error_message = json_decode($error->getMessage(), true);
                if ($error_message) $errors = $error_message;
            }
        }
        $this->view->renderCustomerRegister($errors, $customer_data);
    }

    //CUSTOMER HELPER METHODS:

    private function handleCustomerPost()
    {
        $errors = array();

        $first_name = $this->getAndValidatePost('first_name');
        $last_name = $this->getAndValidatePost('last_name');
        $email = $this->getAndValidatePost('email');
        $password = $this->getAndValidatePost('password');
        $password_confirm = $this->getAndValidatePost('password_confirm');
        if ($password !== $password_confirm) {
            array_push($errors, 'Passwords do not match.');
        }
        $address = $this->getAndValidatePost('address');
        if (empty($email) || empty($password) || empty($password_confirm)) {
            array_push($errors, 'Please fill in all required fields');
        }

        if (count($errors) === 0) {
            $customer_data = array();
            $customer_data['first_name'] = $first_name;
            $customer_data['last_name'] = $last_name;
            $customer_data['email'] = $email;
            $customer_data['password'] = $password;
            $customer_data['address'] = $address;
            return $customer_data;
        } else {
            throw new Exception(json_encode($errors));
        }
    }

    //ADMIN MAIN METHODS:
    //ADMIN HELPER METHODS:

    /**
     *
     */
    private function resolveRoute()
    {
        $page = $_GET["page"] ?? "";

        $function = $this->routes[$page] ?? null; // 'create'

        $this->conditionForExit(!$function);
        echo call_user_func([$this, $function]);
    }

    private function index()
    {
        $this->view->renderHeader("mancave - home");
        echo 'placeholder for landing page';
        $this->view->renderFooter();
    }

    private function getProductsByCategory()
    {
        $category = $this->sanitize($_GET['category']);
        $products = $this->product_model->fetchProductsByCategory($category);
        $this->view->renderProductPage($products);
    }

    private function getProductById()
    {
        $id = $this->sanitize($_GET['id']);
        $product = $this->product_model->fetchProductById($id);

        if (!$product) echo 'Product id does not exist.';
        else $this->view->renderDetailPage($product);
    }

    private function adminIndex()
    {
        $products = $this->product_model->fetchAllProducts();
        $this->view->renderAdminIndexPage($products);
    }

    private function adminProductCreate()
    {
        $product_data = array();
        $errors = array();

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            try {
                $product_data = $this->handleProductPost();
                $this->product_model->createProduct($product_data);
                header("Location: ?page=admin/products");
                exit();
            } catch (Exception $error) {
                $error_message = json_decode($error->getMessage(), true);
                $errors = $error_message;
            }
        }

        $brands = $this->product_model->fetchAllBrands();
        $categories = $this->product_model->fetchAllCategories();
        $this->view->renderAdminProductCreatePage($brands, $categories, $errors);
    }

    private function adminProductUpdate()
    {
        $this->conditionForExit(empty($_GET['id']));

        $id = (int)$this->sanitize($_GET['id']);
        $product_data = array();
        $errors = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            try {
                $product_data = $this->handleProductPost();
                $this->product_model->updateProductById($id, $product_data);
                header('Location: ?page=admin/products');
                exit;
                echo "<pre>";
                var_dump($product_data);
                echo "</pre>";
            } catch (Exception $error) {
                $error_message = json_decode($error->getMessage(), true);
                $errors = $error_message;
            }
        }

        $brands = $this->product_model->fetchAllBrands();
        $categories = $this->product_model->fetchAllCategories();
        $product_data = $this->product_model->fetchProductById($id);
        //TODO: Better error handling
        if (!$product_data) echo 'Product id does not exist.';
        else $this->view->renderAdminProductUpdatePage($brands, $categories, $product_data, $errors);
    }

    private function handleProductPost()
    {
        $errors = array();

        $name = $this->getAndValidatePost('name');
        $price = $this->getAndValidatePost('price', true);
        $description = $this->getAndValidatePost('description');
        $category_id = $this->getAndValidatePost('category_id', true);
        $stock = $this->getAndValidatePost('stock', true);
        $image = $this->getAndValidatePost('image');
        $specification = $this->getAndValidatePost('specification');

        $chosen_brand = $this->getAndValidatePost('brand_id', true);
        $new_brand_chosen = $this->getAndValidatePost('brand_id') === 'NEW';
        $new_brand = $this->getAndValidatePost('new_brand');

        if ((!$new_brand_chosen && $new_brand) || ($new_brand_chosen && !$new_brand) || (!$chosen_brand && !$new_brand)) {
            array_push($errors, "To add a new brand, please pick option 'Add New Brand' and enter a brand name below.");
        } else if ($new_brand_chosen && $new_brand) {
            $product_data['brand_id'] = $this->product_model->createBrand($new_brand);
        } else {
            $product_data['brand_id'] = $chosen_brand;
        }

        if ($name && $price && $category_id) {
            $product_data['name'] = $name;
            $product_data['price'] = $price;
            $product_data['description'] = $description;
            $product_data['category_id'] = $category_id;
            $product_data['stock'] = $stock;
            $product_data['image'] = $image;
            $product_data['specification'] = $specification;
        } else {
            array_push($errors, 'Please fill in all required fields');
        }

        if (count($errors) === 0) {
            return $product_data;
        } else {
            throw new Exception(json_encode($errors));
        }
    }

    /**
     * Expects name of post key, 
     * optional bool (true for int values, default false) 
     * returns value or false
     */
    private function getAndValidatePost($name, $int = false)
    {
        if (isset($_POST[$name])) {
            $value = $this->sanitize($_POST[$name]);
            if ($int) return (int)$value;
            return $value;
        }
        return false;
    }

    private function adminOrderList()
    {
        //TODO: create order functionality
        //$orders = $this->order_model->fetchAllOrders();
        $this->view->renderAdminOrderListPage(/* $orders */);
    }

    private function sanitize($text)
    {
        $text = trim($text);
        $text = stripslashes($text);
        $text = htmlspecialchars($text);
        return $text;
    }

    private function conditionForExit($condition)
    {
        if ($condition) {
            echo "Page not found";
            exit();
        }
    }
}
