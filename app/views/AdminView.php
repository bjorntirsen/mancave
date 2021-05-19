<?php

require_once 'View.php';

class AdminView extends View
{

    // MAIN METHODS:

    public function renderLoginPage()
    {
        $this->renderHead("Admin - Log in");

        include_once "partials/adminLogin.php";

        $this->renderFooter();
    }

    public function renderIndexPage($products, $alerts = [])
    {
        $this->renderHead("Admin - Home");
        $this->renderNav(true);
        $this->renderButton("Add new product", "?page=admin/products/create");

        // other possible errors than "No products to show"?
        if ($alerts) {
            $this->renderAlerts($alerts);
        } else {
            $this->renderListStart(["#", "Name", "Stock", "Edit", "Delete"]);
            $this->renderListItemsProducts($products);
            $this->renderListEnd();
        }
        $this->renderFooter();
    }

    public function renderProductCreatePage($brands, $categories, $alerts)
    {
        $this->renderHead("Admin - Create Product");
        $this->renderNav(true);
        $this->renderButton(
            "Go back to product list",
            "?page=admin",
            "secondary"
        );
        $this->renderButton(
            "Go to order list",
            "?page=admin/orders",
            "secondary"
        );
        $this->renderAlerts($alerts);
        $this->renderForm($brands, $categories);
        $this->renderFooter();
    }

    public function renderProductUpdatePage(
        $brands,
        $categories,
        $product_data,
        $errors = []
    ) {
        $this->renderHead("Admin - Update Product");
        $this->renderNav(true);
        $this->renderButton(
            "Go back to product list",
            "?page=admin",
            "secondary"
        );
        $this->renderAlerts($errors);
        $this->renderForm($brands, $categories, $product_data);
        $this->renderFooter();
    }

    public function renderOrderListPage($orders, $alerts)
    {
        $this->renderHead("Admin - Order List");
        $this->renderNav(true);
        $this->renderButton(
            "Go back to product list",
            "?page=admin",
            "secondary"
        );

        $this->renderAlerts($alerts);
        // avoid rendering list if orders is empty (CODE READABILITY?)
        if (!empty($orders)) {
            $this->renderListStart([
                "#",
                "Date Placed",
                "Customer Name",
                "Status",
                "Change Status",
                "View Order",
                "Delete Order"
            ]);
            $this->renderListItemsOrders($orders);
            $this->renderListEnd();
        }
        $this->renderFooter();
    }

    // HELPER METHODS:

    public function renderListStart($column_name_array)
    {
        include_once "app/views/partials/list/listStart.php";
    }

    public function renderListEnd()
    {
        include_once "app/views/partials/list/listEnd.php";
    }

    /**
     * Receive null by default for create form, or data for update form
     */
    public function renderForm($brands, $categories, $data = null)
    {
        include_once "app/views/partials/form.php";
    }

    public function renderListItemsOrders($orders)
    {
        //TODO: needs to be fixed = "/mancave/?page=admin/orders&id=1&status_id=1&id=3&status_id=2&id=1&status_id=2"
        include_once "app/views/partials/list/orderList.php";
    }

    public function renderListItemsProducts($products)
    {
        include_once "app/views/partials/list/productList.php";
    }
}
