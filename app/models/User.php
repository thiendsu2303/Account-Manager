<?php
class User
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    /**
     * getUsers
     *
     * @return data
     */
    public function getUsers()
    {
        $this->db->query("SELECT * FROM account");
        $result = $this->db->resultSet();
        return $result;
    }

    /**
     * findUserById
     *
     * @param  mixed $id
     * @return data
     */
    public function findUserById($id)
    {
        $this->db->query('SELECT * FROM user WHERE account_id = :id');
        $this->db->bind(':id', $id);
        $data = $this->db->single();
        return $data;
    }

    /**
     * findUserByEmail
     *
     * @param  mixed $email
     * @return data/false
     */
    public function findUserByEmail($email)
    {
        $this->db->query('SELECT * FROM user where email = :email');
        $this->db->bind(':email', $email);

        $row = $this->db->single();
        if ($row != null) {
            return $row;
        } else {
            return false;
        }
    }

    /**
     * register
     *
     * @param  mixed $data
     * @param  mixed $id
     * @return true/false
     */
    public function register($data, $id)
    {
        $this->db->query('INSERT INTO user(fullname, email, account_id) VALUES (:fullname, :email, :acc_id)');
        $this->db->bind(':fullname', $data['fullname']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':acc_id', $id);

        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * updateInfo
     *
     * @param  mixed $data
     * @param  mixed $id
     * @return true/false
     */
    public function updateInfo($data, $id)
    {
        $this->db->query('UPDATE user SET fullname = :fullname, position = :position,
        dob = :dob, phone = :phone, address = :addr WHERE user_id = :id');
        $this->db->bind(':fullname', $data['fullname']);
        $this->db->bind(':position', $data['position']);
        $this->db->bind(':dob', $data['dob']);
        $this->db->bind(':phone', $data['phone_number']);
        $this->db->bind(':addr', $data['addr']);
        $this->db->bind(':id', $id);

        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Update user img
     *
     * @return true/false
     */
    public function updateUserImg($avatar, $id)
    {
        $this->db->query("UPDATE user SET avatar = :ava WHERE account_id = :id");

        // Bind value
        $this->db->bind(':id', $id);
        $this->db->bind(':ava', $avatar);

        $this->db->execute();

        if ($this->db->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }
}
