<?php
namespace Paw;

class Db
{
    protected $mysqli;

    function __construct()
    {
        $this->setMysqli(new \mysqli(MYSQL_DB_HOST, MYSQL_DB_USER, MYSQL_DB_PASSWORD, MYSQL_DB_NAME));
        if ($this->getMysqli()->connect_errno) {
            printf("Connect failed: %s\n", $this->getMysqli()->connect_error);
            exit();
        }
    }
	function insert_deposit($mixin, $mixin_result, $deposit_address, $private_key, $to_address, $revert_on_expire, $time_expiry)
	{
		$revert_on_expire = $revert_on_expire ? 1 : 0;
        $ip = isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $_SERVER["REMOTE_ADDR"];
		
        $result = $this->getMysqli()->query(sprintf("INSERT INTO fresh_deposits (mixin, mixin_result, deposit_address, private_key, to_address, revert_on_expire, time_expiry, time_created) VALUES (%d, %d, '%s','%s', '%s', %d, %d, %d)",
		mysqli_escape_string($this->getMysqli(), $mixin), mysqli_escape_string($this->getMysqli(), $mixin_result), mysqli_escape_string($this->getMysqli(), $deposit_address), mysqli_escape_string($this->getMysqli(), $private_key),
		mysqli_escape_string($this->getMysqli(), $to_address), mysqli_escape_string($this->getMysqli(), $revert_on_expire), $time_expiry, time()));
        if (!$result) {
            printf($this->getMysqli()->error);
            die();
        }

        return $result;
	}
    function get_active_deposits()
    {
        $query = $this->getMysqli()->query(sprintf("SELECT * FROM fresh_deposits WHERE completed=0 ORDER BY id DESC"));

        $rows = FALSE;
        while ($row = $query->fetch_object()) {
            $rows[] = $row;
        }

        return $rows;
    }
    function get_completed_deposits()
    {
        $query = $this->getMysqli()->query(sprintf("SELECT * FROM fresh_deposits WHERE completed=1 and sent_back=0 ORDER BY id DESC"));

        $rows = FALSE;
        while ($row = $query->fetch_object()) {
            $rows[] = $row;
        }

        return $rows;
    }
    function set_deposit_time($id)
    {
        $this->getMysqli()->query(sprintf("UPDATE fresh_deposits SET time_deposited='%d' WHERE id=%d", time(), mysqli_escape_string($this->getMysqli(), $id)));
    }
    function set_deposit_completed_back($id)
    {
        $this->getMysqli()->query(sprintf("UPDATE fresh_deposits SET completed=%d, sent_back=%d, time_completed='%d' WHERE id=%d", 1, 1, time(), mysqli_escape_string($this->getMysqli(), $id)));
    }
    function set_deposit_completed($id, $hash = '')
    {
        $this->getMysqli()->query(sprintf("UPDATE fresh_deposits SET hash='%s', completed=%d, sent_back=%d, time_completed='%d' WHERE id=%d", mysqli_escape_string($this->getMysqli(), $hash), 1, 0, time(), mysqli_escape_string($this->getMysqli(), $id)));
    }
    function set_deposited_amount($id, $amount)
    {
        $this->getMysqli()->query(sprintf("UPDATE fresh_deposits SET deposited_amount='%s' WHERE id=%d", mysqli_escape_string($this->getMysqli(), $amount), mysqli_escape_string($this->getMysqli(), $id)));
    }
    function count_deposits_after($time, $amount)
    {
        $query = $this->getMysqli()->query(sprintf("SELECT count(id) as amount FROM fresh_deposits WHERE deposited_amount='%s' AND time_deposited>%d ORDER BY id DESC", mysqli_escape_string($this->getMysqli(), $amount), mysqli_escape_string($this->getMysqli(), $time)));
		$row = $query->fetch_object();
		
        return $row ? $row->amount : 0;
    }
    function set_deposit_collect_time($id)
    {
        $this->getMysqli()->query(sprintf("UPDATE fresh_deposits SET time_collected='%d' WHERE id=%d", time(), mysqli_escape_string($this->getMysqli(), $id)));
    }





    public function getMysqli()
    {
        return $this->mysqli;
    }

    public function setMysqli(\Mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
        return $this;
    }
}
