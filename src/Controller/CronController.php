<?php
namespace Paw\Controller;

use Paw\AbstractController;
use Paw\Helper;

class CronController extends AbstractController
{
	private $ALLOWED_DEPOSITS = array('1000000','10000000','10000000','10000000');
	
    public function run()
    {
		$this->anonymizePAW();
		$this->collectPAW();
		
		die();
	}
	
	//------------
	
	private function anonymizePAW()
	{
		$deposits = $this->getDb()->get_active_deposits();
		if(!$deposits)
			return;
		
		foreach($deposits as $deposit)
		{
			$node = new \Paw\Node\Client();
			$node->receivePending(DEPOSIT_WALLET, $deposit->private_key);
			
			$raw_balance = $node->getBalance($deposit->deposit_address);
			if($raw_balance->balance > 0)
			{
				if($deposit->time_deposited == 0)
				{
					$this->getDb()->set_deposit_time($deposit->id);
					continue;
				}
				
				$randSeconds = rand(60,600); // to prevent any clear transactions that happend exactly at same time
				$expired = time() > ($deposit->time_expiry - $randSeconds);
				$revert = $deposit->revert_on_expire;
				$balance = \Paw\Helper::raw2den($raw_balance->balance, 'PAW');
				$id = md5('anon-'.$deposit->deposit_address.'-'.$deposit->to_address);
				
				if(($expired && $revert) || array_search($balance, $this->ALLOWED_DEPOSITS) === FALSE)
				{
					$node->sendPawBack(DEPOSIT_WALLET, $deposit->deposit_address, $deposit->private_key);
					$this->getDb()->set_deposit_completed_back($deposit->id);
					continue;
				}
				if($expired && !$revert)
				{
					$result = $node->sendFreshPAW($balance, $deposit->to_address, $id);
					if(!isset($result->error))
						$this->getDb()->set_deposit_completed($deposit->id, $result->block);
				}
				
				if(empty($deposit->deposited_amount))
				{
					$this->getDb()->set_deposited_amount($deposit->id, $balance);
					continue;
				}
				
				$deposit_count_since = $this->getDb()->count_deposits_after($deposit->time_deposited, $balance);
				if($deposit_count_since >= $deposit->mixin_result)
				{
					$result = $node->sendFreshPAW($balance, $deposit->to_address, $id);
					if(!isset($result->error))
						$this->getDb()->set_deposit_completed($deposit->id, $result->block);
				}
			}
		}
	}

	//------------
	
	private function collectPAW()
	{
		$deposits = $this->getDb()->get_completed_deposits();
		if(!$deposits)
			return;
		
		foreach($deposits as $deposit)
		{
			if($deposit->revert_on_expire)
				continue;
			
			$node = new \Paw\Node\Client();
			$node->receivePending(DEPOSIT_WALLET, $deposit->private_key);
			
			$raw_balance = $node->getBalance($deposit->deposit_address);
			$randSeconds = rand(60, 600);
			$collectionTime = $deposit->time_expiry + rand(60, 600);
			
			if($raw_balance->balance > 0 && time() > $collectionTime)
			{
				$node->collectPAW(DEPOSIT_WALLET, $deposit->deposit_address, $deposit->private_key);
				$this->getDb()->set_deposit_collect_time($deposit->id);
			}
		}
	}
}