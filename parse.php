<?php

class parseBackUp 
{
	var $_count;
	var $_total_count;
	var $query;
	var $data = array();
	var $storageDirectory;
	var $object;
  var $applicationId;
  var $apiKey;
  var $masterKey;
  var $sessionToken = '';

	public function __construct($params)
	{
		  $this->storageDirectory = $params->storageDirectory;
    	$this->object = $params->object;
      $this->apiKey = $params->apiKey;
      $this->masterKey = $params->masterKey;
      $this->applicationId = $params->applicationId;

    	$q = $this->getCount();
    	
    	$count = $q->count;

    	if($count>100)
    	{
    		$this->_total_count = round($count / 100);
    		$this->_count = 0;
    	}

      $this->backupObject();
    }

    public function backupObject()
    {
    	$this->fetchObject();
    }

    public function fetchObject()
    {
    	 $skip = $this->_count * 100;
    	 $results = $this->parseObject($skip);
       $this->data = array_merge($this->data,$results);
   		 $this->_count++;
   		 $this->testForCompletion();
    }

    public function testForCompletion()
    {
    	if($this->_count<$this->_total_count)
    	{
    		$this->backupObject();
    	}	else{

        $date = date('m-d-Y');
    		file_put_contents($this->storageDirectory.'/'.$this->object.'-'.$date.'.json', json_encode($this->data));
    	}
    }

    private function parseObject($skip)
    {
    	$curl = 'curl -X GET '
    	.' -H "X-Parse-Application-Id: '.$this->applicationId.'" '
    	.' -H "X-Parse-REST-API-Key: '.$this->apiKey.'" '
      .' -H "X-Parse-Session-Token: '.$this->sessionToken.'" '
      //.' -H "X-Parse-Master-Key: '.$this->masterKey.'" '
      .' --data-urlencode \'limit=100\' '
    	.' --data-urlencode \'skip='.$skip.'\' ';
      $curl .= ($this->object =='User'?' https://api.parse.com/1/users':' https://api.parse.com/1/classes/'.$this->object);
  		$sh = json_decode(shell_exec($curl));
  		if($sh)
  		{
       return $sh->results;
  		}else{
  			return array();
  		}
    }

    private function login()
    {
      $curl = 'curl -X GET '
      .' -H "X-Parse-Application-Id: '.$this->applicationId.'" '
      .' -H "X-Parse-REST-API-Key: '.$this->apiKey.'" '
      .' --data-urlencode \'username=****\'' 
      .' --data-urlencode \'password=****\'' 
      //.' -H "X-Parse-Master-Key: '.$this->masterKey.'" '
      .' https://api.parse.com/1/login';
      //echo $curl;
      $sh = json_decode(shell_exec($curl));
      //print_r($sh);
      if($sh)
      {
       return $sh;
      }else{
        return array();
      }
    }

    private function getCount()
    {
    	$curl = 'curl -X GET '
    	.' -H "X-Parse-Application-Id: '.$this->applicationId.'" '
    	.' -H "X-Parse-REST-API-Key: '.$this->apiKey.'" '
    	.' --data-urlencode \'where={}\''
    	.' --data-urlencode \'limit=0\' '
    	.' --data-urlencode \'count=1\' '
    	.' https://api.parse.com/1/classes/'.$this->object;

    	$sh = json_decode(shell_exec($curl));
  		if($sh)
  		{
  			return $sh;
  		}else{
  			return false;
  		}
    }
}

//SET PARAMS
$params->applicationId = '*********';
$params->apiKey = '**********';
$params->masterKey = '********';
$params->storageDirectory = '/';
$params->object = 'Foo';
//$backup = new parseBackUp($params);


?>