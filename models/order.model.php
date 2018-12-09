<?php

class OrderModel extends Model
{
    public function get($id)
    {
        $id = (int)$id;
        $res = $this->db->query("SELECT * FROM orders WHERE id = $id");
        return isset($res[0]) ? $res[0] : false;
    }

    public function getAll($startPos = 0, $limit = 10, $order = 1, $orderSc = 0)
    {
        $startPos = (int)$startPos;
        $limit = (int)$limit;
        $order = (int)$order;
        $orderSc = (int)$orderSc;
        $orderSc = $orderSc == 0 ? "ASC" : "DESC";

        $res = $this->db->query("SELECT * FROM orders WHERE `show` = 1 ORDER BY $order $orderSc LIMIT $startPos, $limit;");
        return $res;
    }

    public function allCount()
    {
        $res = $this->db->query("SELECT COUNT(*) FROM orders WHERE `show` = 1;");
        return $res[0]['COUNT(*)'];
    }

    public function new($userID, $address, $products)
    {
        $userID = (int)$userID;
        $address = (int)$address;
        $result = true;

        $this->db->getConnection()->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
        $res = $this->db->query("INSERT INTO `orders` (`user`, `address`, `date`) VALUES ($userID, $address, UNIX_TIMESTAMP(NOW()));");
        if (!$res) $result = false;
        $orderID = $this->db->getConnection()->insert_id;

        $sum = 0;
        $productModel = new ProductModel();
        foreach ($products as $product) {
            $info = $productModel->get($product['product']);
            if (!$info) continue;

            $price = $info['new_price'] ? $info['new_price'] : $info['price'];
            $productID = $product['product'];
            $col = $product['col'];
            $sum += $price * $col;
            $res = $this->db->query("INSERT INTO `order_products` (`order`, `product`, `price`, `col`) VALUES ($orderID, $productID, $price, $col);");
            if (!$res) $result = false;

            $newCol = $info['col'] - $col;
            $res = $productModel->updateCol($productID, max(0, $newCol));
            if (!$res) $result = false;
        }

        $this->db->getConnection()->commit();
        if ($result) {
            setcookie('cart', '', time() - 60, '/');

            $order = $this->get($orderID);
            $userModel = new UserModel();
            $address = $userModel->getAddress($order['address'], $order['user']);

            $user = $userModel->getById($order['user']);
            $data = array(
                '-ID-'      => $order['id'],
                '-DATE-'    => dateFormat($order['date']),
                '-SUM-'     => _p($sum),
                '-ADDRESS-' => $address['address'],
                '-USER-'    => $user['last_name'] . ' ' . $user['first_name'] . ' ' . $user['middle_name'],
                '-EMAIL-'   => $user['email']
            );
            Mail::send(Config::get('email.order'), 'New order', 'order', $data);
        }

        return $result;
    }

    public function getByUser($id, $userid)
    {
        $id = (int)$id;
        $userid = (int)$userid;
        $res = $this->db->query("SELECT * FROM orders WHERE id = $id AND `user` = $userid AND `show` = 1;");
        return isset($res[0]) ? $res[0] : false;
    }

    public function products($id)
    {
        $id = (int)$id;
        $res = $this->db->query("SELECT * FROM order_products WHERE `order` = $id");
        return $res;
    }

    public function delete($id, $userid)
    {
        $id = (int)$id;
        $userid = (int)$userid;
        $res = $this->db->query("UPDATE `orders` SET `show` = 0 WHERE `id` = $id AND `user` = $userid");
        $rows = $this->db->getConnection()->affected_rows;
        $rows = $rows > 0 ? true : false;
        return $res ? $rows : false;
    }

    public function editStatus($id, $status) {
        $id = (int)$id;
        $status = (int)$status;

        $res = $this->db->query("UPDATE `orders` SET `status` = $status WHERE `id` = $id;");
        return $res;
    }
}