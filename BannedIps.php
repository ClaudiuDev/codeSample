<?php
class Needs_BannedIps
{
	protected $_ip;
	
	public function __construct($ip)
	{		
		$this->_ip	= $ip;
	}
	
	/**
	 * returns the number of failed logins from the same ip
	 * 
	 * @return int
	 */
	public function getFailedLogins()
	{
		$modelFailed = new Default_Model_Failedloginips();
		$select = $modelFailed->getMapper()->getDbTable()->select()
						->where('ip = ?',$this->_ip);
		$modelFailed->fetchRow($select);
		
		return $modelFailed->getFailed();
	}
	
	/**
	 * checks if the ip is banned
	 * 
	 * @return boolean
	 */
	public function checkBanned()
	{  
		$modelBanned = new Default_Model_Adminbanned();
		$select = $modelBanned->getMapper()->getDbTable()->select()
						->where('ip = ?',$this->_ip);
		$modelBanned->fetchRow($select);
		
		// type 0 = temporary ban, if the ban period has not passed, access is denied / 1 = permanent ban , access denied
		if(($modelBanned->getType() == 0 && date( 'Y-m-d H:i:s' , $modelBanned->getExpirationDate()) > date('Y-m-d H:i:s')) || $modelBanned->getType() == 1)
		{
			return true;
		}
		return false;
	}
	
	/**
	 * returns the number of temporary bans on one ip
	 * 
	 * @return int
	 */
	public function checkTemporaryBan()
	{  
		$modelBanned = new Default_Model_Adminbanned();
		$select = $modelBanned->getMapper()->getDbTable()->select()
						->where('ip = ?',$this->_ip);
		$modelBanned->fetchRow($select);
		 
		return $modelBanned->getTemporaryBan();
	}
	
	/**
	 * increments number of failed logins
	 */
	public function incFailedLogin()
	{  
		$modelFailed = new Default_Model_Failedloginips();
		$select = $modelFailed->getMapper()->getDbTable()->select()
						->where('ip = ?',$this->_ip);
		$modelFailed->fetchRow($select);
		
		$modelFailed->setIp($this->_ip);
		$modelFailed->setFailed($modelFailed->getFailed() + 1);
		$modelFailed->save();
	}
	
	/**
	 * increments temporary ban
	 */
	public function saveTemporaryBan()
	{  
		$modelBanned = new Default_Model_Adminbanned();
		$select = $modelBanned->getMapper()->getDbTable()->select()
						->where('ip = ?',$this->_ip);
		$modelBanned->fetchRow($select);
		// where $nr is the number of temporary bans after the failed login attempt
		$nr = $modelBanned->getTemporaryBan() + 1;

		$modelBanned->setIp($this->_ip);
		$modelBanned->setTemporaryBan($nr);
		$modelBanned->setType(0);
		$modelBanned->setExpirationDate(date('Y-m-d H:i:s', strtotime('+5 min')));
		$modelBanned->save();
		
		$this->deleteFailedLogin($this->_ip);
	}
	
	/**
	 * check number of failed connections on the same ip
	 */
	public function checkFailedConnections(){
		
		if($this->getFailedConnections()+1 == 3)
		{
			//increments the number of temporary bans
			$this->saveTemporaryBan();
			if($this->checkTemporaryBan() >= 5)
			{
				$this->savePermanentBan();
			}
			$this->deleteFailedLogin();
		}
		else
		{
			// increment number of failed connections
			$this->incFailedLogin();
		}
		
	}
	
	/**
	 * permanently bans ip and delete failed login attempts
	 */
	public function savePermanentBan()
	{  
		$modelBanned = new Default_Model_Adminbanned();
		$select = $modelBanned->getMapper()->getDbTable()->select()
						->where('ip = ?',$this->_ip);
		$modelBanned->fetchRow($select);
		
		$modelBanned->setIp($this->_ip);
		$modelBanned->setType(1); //type = 1 - permanent ban
		$modelBanned->setExpirationDate(NULL);
		$modelBanned->setTemporaryBan($modelBanned->getTemporaryBan());
		$modelBanned->save();
		
		$this->deleteFailedLogin($this->_ip);
	}
	
	/**
	 * delete failed login attempts
	 * 
	 * @return boolean
	 */
	public function deleteFailedLogin()
	{
		$deleted = NULL;
		
		$modelFailed = new Default_Model_Failedloginips();
		$select = $modelFailed->getMapper()->getDbTable()->select()
						->where('ip = ?',$this->_ip);
		$modelFailed->fetchRow($select);
		
		if($modelFailed->getId())
		{
			$deleted = $modelFailed->delete();
		}
		return $deleted;	
	}
	
	/**
	 * delete ban
	 * 
	 * @return boolean
	 */
	public function deleteBan()
	{
		$deleted = NULL;
		
		$modelBanned = new Default_Model_Adminbanned();
		$select = $modelBanned->getMapper()->getDbTable()->select()
						->where('ip = ?',$this->_ip);
		$modelBanned->fetchRow($select);
		
		if($modelBanned->getId())
		{
			$deleted = $modelBanned->delete();
		}
		return $deleted;
	}
	
}

