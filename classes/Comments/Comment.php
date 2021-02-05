<?php
namespace Comments;

class Comment extends \Cetera\Material
{
    const TABLE = 'comments';
		
	public static function getById($id, $type = 0, $table = NULL)
    {
		return parent::getById($id,  self::getObjectDefinition() );
    }	
	
	public static function getObjectDefinition()
	{
		return \Cetera\ObjectDefinition::findByAlias(static::TABLE);
	}        
        
	public function getNickname()
	{
		if (isset($this->fields['nickname'])) return $this->fields['nickname'];
		if ($this->autor && $this->autor->name) return $this->autor->name;
		if ($this->autor && $this->autor->login) return $this->autor->login;
        return null;
	}
	
	public function getAvatar()
	{
		return '/plugins/comments/images/user.png';
	}
	
	public function getMaterial()
	{
		try 
		{
			return \Cetera\Material::getById( $this->material_id, $this->material_type );
		} catch (\Exception $e) {
			return null;
		}
	}	
	
}