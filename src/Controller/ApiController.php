<?php
namespace Paw\Controller;

use Paw\AbstractController;
use Paw\Utils;
use Paw\Node;
use Paw\Helper;
use Paw\Db;

class ApiController extends AbstractController
{
	public function createDepositAccount()
	{
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Authorization, Origin');
		header('Access-Control-Allow-Methods:  POST, PUT, GET');
		header("Content-Type: application/json");
		
		$json = FALSE;
		$json['result'] = 'error';
		
		$settings = $this->getDepositSettings();
		if(isset($settings['err']))
			$json['error'] = $settings['err'];
		else
		{
			$node = new \Paw\Node\Client();
			$wallet = $node->newDepositAccount();
			$expiry = time() + ($settings['max_wait'] * 60 * 60);
			$mixin_rand = rand(1, $settings['mixin']); // result of after how many deposit it's being sent back
			$this->getDb()->insert_deposit($settings['mixin'], $mixin_rand, $wallet['account'], $wallet['private_key'], $settings['to_address'], $settings['revert_on_expire'], $expiry);
			
			$json['result'] = 'success';
			$json['address'] = $wallet['account'];
			$json['fee'] = SERVICE_FEE;
		}
		
		
		echo json_encode($json);
		die();
	}
	
	private function getDepositSettings()
	{
		$max_wait = isset($_GET['max_wait']) ? $_GET['max_wait'] : 12;
		$mixin = isset($_GET['mixin']) ? intval($_GET['mixin']) : 5;
		$to_address = isset($_GET['to_address']) ? $_GET['to_address'] : FALSE;
		$revert_on_expire = isset($_GET['revert_on_expire']) && $_GET['revert_on_expire'] == '1' ? TRUE : FALSE;
		
		$data = false;
		$data['max_wait'] = $max_wait;
		$data['mixin'] = $mixin;
		$data['to_address'] = $to_address;
		$data['revert_on_expire'] = $revert_on_expire;
		
		$node = new \Paw\Node\Client();
		
		if($max_wait < 1 || $max_wait > 720)
			$data['err'] = 'Set a maximum wait between 1 and 720';
		else if($mixin < 1 || $mixin > 25)
			$data['err'] = 'Set a mixin between 1 and 25';
		else if(!$to_address)
			$data['err'] = 'Specify an output address';
		else if(!$node->validAccount($to_address))
			$data['err'] = 'Output address is invalid';
			
		return $data;
	}
}