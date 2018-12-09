<?php

class MainController extends Controller
{
    public function __construct(array $data = array())
    {
        parent::__construct($data);
        $this->models->product = new ProductModel();
        $this->models->photo = new PhotoModel();
        $this->models->stats = new StatsModel();
    }

    public function index()
    {
        $products = $this->models->product->getNewProducts(9);
        $_products = array();
        if ($products) {
            foreach ($products as $product) {
                $mainPhoto = $product['main_photo'];
                if (!$mainPhoto) {
                    $mainPhoto = Config::get('photo.default');
                }
                $product['main_photo'] = Config::get('storage.photo') . $mainPhoto;
                $_products[] = $product;
            }
            $this->data['products'] = $_products;
        } else {
            $this->data['products'] = null;
        }
    }

    public function dashboard_index()
    {
        // If not admin
        if (Session::get('role') != 1 ) {
            App::getRouter()->redirect('/');
            exit();
        }

        $this->data['orders'] = $this->models->stats->getOrders();
        $this->data['users'] = $this->models->stats->getUsers();
        $this->data['products'] = $this->models->stats->getProducts();
    }

}