<?php

class UsersController extends Controller
{
    public function __construct(array $data = array())
    {
        parent::__construct($data);
        $this->models->user = new UserModel();
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
        $count = $this->models->user->allCount();
        if ($count > 0) {
            // Pagination

            $productsOnPage = Config::get('products.page');

            $pagesCount = ceil($count / $productsOnPage);
            if ($pagesCount < 1) $pagesCount = 1;

            if ($page > $pagesCount || $page < 1) {
                App::getRouter()->redirect('/dashboard/users/view');
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

            $users = $this->models->user->getAll(($page - 1) * $productsOnPage, $productsOnPage, 7, 1);
            if ($users) {
                for ($i = 0; $i < count($users); $i++) {
                    $users[$i]['role'] = $users[$i]['role'] == 1 ? 'Администратор' : 'Пользователь';
                }
                $this->data['users'] = $users;
            } else {
                $this->data['users'] = null;
            }
        } else {
            $this->data['users'] = null;
        }
    }

    public function dashboard_edit()
    {
        // If not admin
        if (Session::get('role') != 1) {
            App::getRouter()->redirect('/');
            exit();
        }

        $id = (int)$this->params[0];

        if (isset($_POST['login']) && isset($_POST['ln']) && isset($_POST['fn'])) {
            $email = $_POST['login'];
            $ln = $_POST['ln'];
            $fn = $_POST['fn'];
            $mn = $_POST['mn'];
            if (empty($mn)) $mn = '';

            if ($email && $ln && $fn) {

                $update = $this->models->user->update($id, $email, $fn, $ln, $mn);
                if ($update !== false) {
                    Session::setMessage('Успешно изменено', 'success');
                    App::getRouter()->redirect('/dashboard/users/view');
                    exit();
                } else {
                    Session::setMessage('Ошибка изменения', 'error');
                }

            } else {
                Session::setMessage('Введите все данные', 'error');
            }

            App::getRouter()->redirect('/dashboard/user/add');
        }

        $this->data['user'] = $this->models->user->getById($id);
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
            $res = $this->models->user->delete($id);
            if ($res) {
                Session::setMessage('Успешно удалено', 'success');
            } else {
                Session::setMessage('Ошибка удаления', 'danger');
            }
        }
        App::getRouter()->redirect('/dashboard/users/view');
    }

    public function dashboard_add()
    {
        // If not admin
        if (Session::get('role') != 1) {
            App::getRouter()->redirect('/');
            exit();
        }

        if (isset($_POST['login']) && isset($_POST['password']) && isset($_POST['password2']) && isset($_POST['ln']) && isset($_POST['fn'])) {
            $email = $_POST['login'];
            $password = $_POST['password'];
            $password2 = $_POST['password2'];
            $ln = $_POST['ln'];
            $fn = $_POST['fn'];
            $mn = $_POST['mn'];
            if (empty($mn)) $mn = '';

            if ($email && $password && $password2 && $ln && $fn) {

                if ($password == $password2) {
                    $reg = $this->models->user->register($email, $password, $fn, $ln, $mn);
                    if ($reg !== false) {
                        Session::setField('login.email', $email);
                        Session::setMessage('Вы успешно зарегистрировались. Теперь можно войти', 'success');
                        App::getRouter()->redirect('/dashboard/users/view');
                        exit();
                    } else {
                        Session::setMessage('Ошибка регистрации', 'error');
                    }
                } else {
                    Session::setMessage('Пароли не совпадают', 'error');
                }

            } else {
                Session::setMessage('Введите все данные', 'error');
            }

            Session::setField('reg.email', $email);
            Session::setField('reg.fn', $fn);
            Session::setField('reg.ln', $ln);
            Session::setField('reg.mn', $mn);

            App::getRouter()->redirect('/dashboard/user/add');
        }
    }
}