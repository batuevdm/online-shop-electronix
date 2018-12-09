<?php

class CartController extends Controller
{
    public function __construct(array $data = array())
    {
        parent::__construct($data);
        $this->models->cart = new CartModel();
        $this->models->order = new OrderModel();
        $this->models->product = new ProductModel();
        $this->models->user = new UserModel();
        $this->models->photo = new PhotoModel();
    }

    public function index()
    {

        $cart = $this->models->cart->get();

        $total = 0;
        $products = array();
        foreach ($cart as $item) {
            $_product = $this->models->product->get($item['product']);
            $products[$item['product']] = $item;
            $products[$item['product']]['product'] = $_product;

            $photo = $_product['main_photo'] ? $_product['main_photo'] : Config::get('photo.default');
            $products[$item['product']]['product']['main_photo'] = Config::get('storage.photo') . $photo;

            $total += ($_product['new_price'] ? $_product['new_price'] : $_product['price']) * $item['col'];
        }

        $this->data['products'] = $products;
        $this->data['totalPrice'] = $total;

        $this->data['isLogged'] = (Session::get('userid') != null);

    }

    public function ajax_add()
    {
        $data = array();

        $cart = isset($_COOKIE['cart']) ? $_COOKIE['cart'] : '{}';
        if (!isJson($cart)) {
            $cart = '{}';
        }

        $cart = json_decode($cart, true);

        if (isset($this->params[0]) && isset($this->params[1])) {
            $productID = (int)$this->params[0];
            $col = (int)$this->params[1];
            if ($col < 1) $col = 1;

            if ($product = $this->models->product->get($productID)) {
                $max = $this->models->product->count($productID);

                $time = time() + 365 * 24 * 60 * 60;

                $c = isset($cart[$productID]['col']) ? $cart[$productID]['col'] : 0;
                if (($c + $col) > $max) {
                    $data['status'] = 'fail';
                    $data['message'] = 'Количество товаров больше, чем есть на складе';
                } else {
                    $cart[$productID]['col'] += $col;
                    setcookie('cart', json_encode($cart), $time, '/');

                    $data['status'] = 'ok';
                    $data['message'] = 'Товар добавлен в корзину';
                }
            }
        }

        header('Content-type: application/json');
        echo json_encode($data);
        exit();
    }

    public function ajax_update()
    {
        $data = array();

        if (isset($this->params[0]) && isset($this->params[1])) {
            $productID = (int)$this->params[0];
            $col = (int)$this->params[1];

            $product = $this->models->product->get($productID);
            if ($col > $product['col']) $col = $product['col'];
            if ($col < 1) $col = 1;
            $res = $this->models->cart->update($productID, $col);
            if ($res === true) {
                $data['status'] = 'ok';
                $data['col'] = $col;

                $cart = $this->models->cart->get();
                $total = 0;
                foreach ($cart as $item) {
                    $product = $this->models->product->get($item['product']);
                    $total += ($product['new_price'] ? $product['new_price'] : $product['price']) * $item['col'];
                }

                $data['totalPrice'] = $total;
            } else {
                $data['status'] = 'fail';
                $data['message'] = 'Query: product or user not exists';
            }
        } else {
            $data['status'] = 'fail';
            $data['message'] = 'Product and col id required';
        }

        header('Content-type: application/json');
        echo json_encode($data);
        exit();
    }

    public function ajax_delete()
    {
        $data = array();

        if (isset($this->params[0])) {
            $productID = (int)$this->params[0];

            $res = $this->models->cart->delete($productID);
            if ($res === true) {
                $data['status'] = 'ok';
                $data['message'] = 'Товар удален из корзины';

                $cart = $this->models->cart->get();
                $total = 0;
                $products = array();
                foreach ($cart as $item) {
                    $_product = $this->models->product->get($item['product']);
                    $products[$item['product']] = $item;
                    $products[$item['product']]['product'] = $_product;

                    $photo = $this->models->photo->get($_product['main_photo']) ? $this->models->photo->get($_product['main_photo']) : Config::get('photo.default');
                    $products[$item['product']]['product']['main_photo'] = $photo;

                    $total += ($_product['new_price'] ? $_product['new_price'] : $_product['price']) * $item['col'];
                }

                $data['products'] = $products;
                $data['totalPrice'] = $total;
            } else {
                $data['status'] = 'fail';
                $data['message'] = 'Query: product or user not exists';
            }
        } else {
            $data['status'] = 'fail';
            $data['message'] = 'Product and col id required';
        }


        header('Content-type: application/json');
        echo json_encode($data);
        exit();
    }

    public function order()
    {
        if (!Session::get('userid')) {
            App::getRouter()->redirect('/account/login?next=/cart/order');
            exit();
        }

        $cart = $this->models->cart->get();

        if (count($cart) < 1) {
            App::getRouter()->redirect('/cart');
            exit();
        }

        if (isset($_POST['address']) && isset($_POST['address-field'])) {
            $address = $_address = trim($_POST['address']);
            if ($address === 'new') {
                $address = trim($_POST['address-field']);
                if ($address) {
                    $res = $this->models->user->addAddress(Session::get('userid'), $address);
                    if ($res) {
                        $address = $this->models->user->getAddressByName($address, Session::get('userid'));
                        if ($address) {
                            $address = $address['id'];
                        } else {
                            $address = '';
                        }
                    }
                    else {
                        Session::setMessage('Ошибка добавления адреса', 'error');
                    }
                }
            }
            if ($address) {
                $res = $this->models->order->new(Session::get('userid'), $address, $cart);
                if ($res) {
                    Session::setField('order.success', true);
                    App::getRouter()->redirect('/');
                } else {
                    Session::setMessage('Ошибка добавления заказа', 'error');
                }
            } else {
                Session::setMessage('Введите адрес', 'error');
            }

            Session::setField('order.address', $_address);
            Session::setField('order.address.field', $address);

            App::getRouter()->redirect('/cart/order');

        }

        $total = 0;
        $products = array();
        foreach ($cart as $item) {
            $_product = $this->models->product->get($item['product']);
            $products[$item['product']] = $item;
            $products[$item['product']]['product'] = $_product;
            $products[$item['product']]['product']['price'] = $_product['new_price'] ? $_product['new_price'] : $_product['price'];

            $total += ($_product['new_price'] ? $_product['new_price'] : $_product['price']) * $item['col'];
        }

        $user = $this->models->user->getById(Session::get('userid'));
        $addresses = $this->models->user->addresses(Session::get('userid'));

        $this->data['products'] = $products;
        $this->data['totalPrice'] = $total;
        $this->data['user'] = $user;
        $this->data['addresses'] = $addresses;
    }

}