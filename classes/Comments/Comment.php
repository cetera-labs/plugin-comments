<?php
namespace Comments;

class Comment extends \Cetera\Material
{
		
	public function getNickname()
	{
		if ($this->fields['nickname']) return $this->fields['nickname'];
		if ($this->autor->name) return $this->autor->name;
		if ($this->autor->login) return $this->autor->login;
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