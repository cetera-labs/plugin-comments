<?php
namespace Comments;

class Plugin extends \Cetera\ObjectPlugin
{
	use \Cetera\DbConnection;
	
	protected static $objectDefinition = null;
	
	public static function getCommentsObjectDefinition()
	{
		if (!self::$objectDefinition)
		{
			self::$objectDefinition = \Cetera\ObjectDefinition::findByAlias('comments');
		}
		return self::$objectDefinition;
	}
	
	public function getCommentsCount()
	{
		return $this->getComments()->getCountAll();
	}	
	
	public function getComments()
	{
		return $this->getCommentsObjectDefinition()->getMaterials()
			->where('material_type = '.$this->object->objectDefinition->id.' and material_id = '.$this->object->id)
			->orderBy('dat', 'ASC');
	}
	
	public function getRatingCount()
	{
		$list = $this->getComments()->where('rating > 0')->select('COUNT(rating) as cnt');
		return $list->current()->cnt;		
	}
	
	public function getRatingTotal()
	{
		$list = $this->getComments()->where('rating > 0')->select('SUM(rating) as sum');
		return $list->current()->sum;		
	}	

	public function getRatingAverage()
	{
		$list = $this->getComments()->where('rating > 0')->select(array('SUM(rating) as sum','COUNT(rating) as cnt'));
		if (!$list->current()->cnt) return 0;
		return $list->current()->sum / $list->current()->cnt;
	}	
	
	public function addComment($text, $publish = true, $user = null, $nickname = null, $rating = null, $email = null )
	{		
				
		if (!$user) $user = \Cetera\Application::getInstance()->getUser();
		
		if (!$user)
	    {
			$user = new \Cetera\User\Anonymous();
		}
		else 
		{
			if ($user->name) 
				$nickname = $user->name;
				else $nickname = $user->login;
				
			$email = $user->email;
		}
		
		if (!$nickname) $nickname = $user->login;
		
		$m = \Cetera\Material::fetch(array(
			'name'     => $this->object->name,
			'comment'  => $text,
			'idcat'    => CATALOG_VIRTUAL_HIDDEN,
			'autor'    => $user->id,
			'publish'  => $publish,
			'material_id' => $this->object->id,
			'material_type' => $this->object->objectDefinition->id,
			'alias'    => time(),
			'nickname' => $nickname,
			'email'    => $email,
			'rating'   => $rating,
			'ip'       => $_SERVER['REMOTE_ADDR'],
		), $this->getCommentsObjectDefinition() );
		
		$m->save();	
		
		return $m;
		
	}		

}