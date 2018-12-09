<?php

class ProductsController extends Controller
{
    public function __construct(array $data = array())
    {
        parent::__construct($data);
        $this->models->product = new ProductModel();
        $this->models->photo = new PhotoModel();
        $this->models->categories = new CategoriesModel();
    }

    public function category()
    {
        $params = $this->getParams();
        if (!isset($params[0])) {
            App::getRouter()->redirect('/');
        }

        $id = (int)$params[0];

        if (!$this->models->categories->exists($id)) {
            App::getRouter()->redirect('/');
        }

        $this->data['categoryId'] = $id;
        $this->data['category'] = $this->models->categories->name($id);

        $subs = $this->models->categories->subcategories($id);
        if ($subs) {
            $this->data['subcategories'] = array();
            foreach ($subs as $sub) {
                $sub['image'] = $sub['image'] ? $sub['image'] : Config::get('photo.default');
                $sub['image'] = Config::get('storage.photo') . $sub['image'];
                $sub['col'] = $this->models->categories->productsCount($sub['id']);
                $this->data['subcategories'][] = $sub;
            }
        } else {
            // Pagination

            $page = 1;
            $productsOnPage = Config::get('products.page');
            if (isset($params[1])) {
                $page = (int)$params[1];
            }
            $count = $this->models->categories->productsCount($id);
            $pagesCount = ceil($count / $productsOnPage);
            if ($pagesCount < 1) $pagesCount = 1;

            if ($page > $pagesCount || $page < 1) {
                App::getRouter()->redirect('/products/category/' . $id);
            }

            $pagesShow = Config::get('pagination.pages');

            $left = $page - 1;
            if ($left < floor($pagesShow / 2)) $start = 1;
            else $start = $page - floor($pagesShow / 2);
            $end = $start + $pagesShow - 1;
            if ($end > $pagesCount) {
                $start -= ($end - $pagesCount);
                $end = $pagesCount;
                if ($start < 1) $start = 1;
            }

            $this->data['pg'] = array(
                'count' => $pagesCount,
                'current' => $page,
                'start' => $start,
                'end' => $end
            );
            // End pagination

            $products = $this->models->product->getByCategory($id, ($page - 1) * $productsOnPage, $productsOnPage);
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

        $breadcrumbs = array();

        $parent = $this->models->categories->parent($id);
        while ($parent) {
            array_unshift($breadcrumbs, array($parent, $this->models->categories->name($parent)));
            $parent = $this->models->categories->parent($parent);
        }

        $this->data['breadcrumbs'] = $breadcrumbs;
    }

    public function product()
    {
        $params = $this->getParams();
        if (!isset($params[0])) {
            App::getRouter()->redirect('/');
        }

        $id = (int)$params[0];
        $product = $this->models->product->get($id);

        if ($product) {
            $specs = $this->models->product->getSpecs($id);
            $photos = $this->models->product->getPhotos($id);
            $mainPhoto = $product['main_photo'];
            if (!$mainPhoto) {
                $mainPhoto = Config::get('photo.default');
            }

            $product['main_photo'] = Config::get('storage.photo') . $mainPhoto;
            $this->data['product'] = $product;
            $this->data['specs'] = $specs;
            $this->data['photos'] = $photos;

            $category = $product['category'];
            $breadcrumbs = array(
                array($category, $this->models->categories->name($category))
            );

            $parent = $this->models->categories->parent($category);
            while ($parent) {
                array_unshift($breadcrumbs, array($parent, $this->models->categories->name($parent)));
                $parent = $this->models->categories->parent($parent);
            }

            $this->data['breadcrumbs'] = $breadcrumbs;

        } else {
            App::getRouter()->redirect('/');
        }
    }

    public function search()
    {
        $getParams = $this->getGetParams();
        $params = $this->getParams();
        $query = trim($getParams['q']);

        if (empty($query)) App::getRouter()->redirect('/');

        $this->data['query'] = $query;

        $page = 1;
        if (isset($params[0])) {
            $page = (int)$params[0];
        }
        if ($page < 1) $page = 1;
        $count = $this->models->product->searchCount($query);
        if ($count > 0) {
            // Pagination

            $productsOnPage = Config::get('products.page');

            $pagesCount = ceil($count / $productsOnPage);
            if ($pagesCount < 1) $pagesCount = 1;

            if ($page > $pagesCount || $page < 1) {
                App::getRouter()->redirect('/products/search/?q=' . $query);
            }

            $pagesShow = Config::get('pagination.pages');

            $left = $page - 1;
            if ($left < floor($pagesShow / 2)) $start = 1;
            else $start = $page - floor($pagesShow / 2);
            $end = $start + $pagesShow - 1;
            if ($end > $pagesCount) {
                $start -= ($end - $pagesCount);
                $end = $pagesCount;
                if ($start < 1) $start = 1;
            }

            $this->data['pg'] = array(
                'count' => $pagesCount,
                'current' => $page,
                'start' => $start,
                'end' => $end
            );
            // End pagination

            $products = $this->models->product->search($query, ($page - 1) * $productsOnPage, $productsOnPage);
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
        } else {
            $this->data['products'] = null;
        }
    }

    public function dashboard_view()
    {
        // If not admin
        if (Session::get('role') != 1) {
            App::getRouter()->redirect('/');
            exit();
        }

        $params = $this->getParams();
        $page = 1;
        if (isset($params[0])) {
            $page = (int)$params[0];
        }
        if ($page < 1) $page = 1;
        $count = $this->models->product->allCount([0, 1], 0);
        if ($count > 0) {
            // Pagination

            $productsOnPage = Config::get('products.page');

            $pagesCount = ceil($count / $productsOnPage);
            if ($pagesCount < 1) $pagesCount = 1;

            if ($page > $pagesCount || $page < 1) {
                App::getRouter()->redirect('/dashboard/products/view');
            }

            $pagesShow = Config::get('pagination.pages');

            $left = $page - 1;
            if ($left < floor($pagesShow / 2)) $start = 1;
            else $start = $page - floor($pagesShow / 2);
            $end = $start + $pagesShow - 1;
            if ($end > $pagesCount) {
                $start -= ($end - $pagesCount);
                $end = $pagesCount;
                if ($start < 1) $start = 1;
            }

            $this->data['pg'] = array(
                'count' => $pagesCount,
                'current' => $page,
                'start' => $start,
                'end' => $end
            );
            // End pagination

            $products = $this->models->product->getAll(($page - 1) * $productsOnPage, $productsOnPage, 1, 1, [0, 1], 0);
            $_products = array();
            if ($products) {
                foreach ($products as $product) {
                    $product['category'] = $this->models->categories->name($product['category']);
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
        } else {
            $this->data['products'] = null;
        }
    }

    public function dashboard_add()
    {
        // If not admin
        if (Session::get('role') != 1) {
            App::getRouter()->redirect('/');
            exit();
        }

        if (isset($_POST['name']) && isset($_POST['price']) && isset($_POST['col']) && isset($_POST['category'])) {
            $name = htmlspecialchars(trim($_POST['name']));
            $desc = htmlspecialchars(trim($_POST['desc']));
            $category = (int)$_POST['category'];
            $price = (int)$_POST['price'];
            $newPrice = (int)$_POST['new-price'];
            $newPrice = $newPrice == 0 ? NULL : $newPrice;
            $col = (int)$_POST['col'];
            $hide = (int)$_POST['hide'];

            $specs = array(
                'name' => $_POST['spec-name'],
                'value' => $_POST['spec-value']
            );
            $files = $_FILES;

            $res = $this->models->product->add($name, $desc, $price, $newPrice, $category, $col, $files, $specs, $hide);
            if ($res === true) {
                Session::setMessage('Товар успешно добавлен', 'success');
                App::getRouter()->redirect('/dashboard/products/view');
            } else {
                $res = $res ? $res : 'Неизвестная ошибка';
                Session::setMessage($res, 'danger');

                Session::setField('product.name', $name);
                Session::setField('product.desc', $desc);
                Session::setField('product.category', $category);
                Session::setField('product.price', $price);
                Session::setField('product.newPrice', $newPrice);
                Session::setField('product.col', $col);
                Session::setField('product.hide', $hide);
                Session::setField('product.specs', $specs);

                App::getRouter()->redirect('/dashboard/products/add');
            }
        }

        $categories = $this->models->categories->getAll();
        $this->data['categories'] = $categories;

        $specs = $this->models->product->getAllSpecs();
        $this->data['specs'] = $specs;

    }

    public function dashboard_edit()
    {
        // If not admin
        if (Session::get('role') != 1) {
            App::getRouter()->redirect('/');
            exit();
        }

        $id = (int)$this->params[0];
        $product = $this->models->product->get($id, [0, 1], 0);

        if ($product) {

            if (isset($_POST['name']) && isset($_POST['price']) && isset($_POST['col']) && isset($_POST['category'])) {
                $name = htmlspecialchars(trim($_POST['name']));
                $desc = htmlspecialchars(trim($_POST['desc']));
                $category = (int)$_POST['category'];
                $price = (int)$_POST['price'];
                $newPrice = (int)$_POST['new-price'];
                $newPrice = $newPrice == 0 ? NULL : $newPrice;
                $col = (int)$_POST['col'];
                $hide = (int)$_POST['hide'];
                $delPhotos = $_POST['del-photos'];

                $specs = array(
                    'name'  => $_POST['spec-name'],
                    'value' => $_POST['spec-value']
                );
                $files = $_FILES;

                $res = $this->models->product->update($id, $name, $desc, $price, $newPrice, $category, $col, $files, $delPhotos, $specs, $hide);
                if ($res === true) {
                    Session::setMessage('Товар успешно изменен', 'success');
                    App::getRouter()->redirect('/dashboard/products/view');
                } else {
                    $res = $res ? $res : 'Неизвестная ошибка';
                    Session::setMessage($res, 'danger');

                    Session::setField('product.name', $name);
                    Session::setField('product.desc', $desc);
                    Session::setField('product.category', $category);
                    Session::setField('product.price', $price);
                    Session::setField('product.newPrice', $newPrice);
                    Session::setField('product.col', $col);
                    Session::setField('product.hide', $hide);
                    Session::setField('product.specs', $specs);

                    App::getRouter()->redirect('/dashboard/products/edit/' . $id);
                }
            }

            $categories = $this->models->categories->getAll();
            $this->data['categories'] = $categories;

            $specs = $this->models->product->getAllSpecs();
            $this->data['allSpecs'] = $specs;

            $this->data['product'] = $product;
            $this->data['specs'] = $this->models->product->getSpecs($id);
            $this->data['photos'] = $this->models->product->getPhotos($id);

        } else {
            App::getRouter()->redirect('/dashboard/products/view');
        }
    }

    public function dashboard_delete()
    {
        // If not admin
        if (Session::get('role') != 1) {
            App::getRouter()->redirect('/');
            exit();
        }

        if (isset($this->params[0])) {
            $id = (int)$this->params[0];
            $res = $this->models->product->delete($id);
            if ($res) {
                Session::setMessage('Успешно удалено', 'success');
            } else {
                Session::setMessage('Ошибка удаления', 'danger');
            }
        }
        App::getRouter()->redirect('/dashboard/products/view');
    }
}