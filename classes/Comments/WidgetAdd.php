<?php
namespace Comments;

class WidgetAdd extends \Cetera\Widget\Templateable
{
	use \Cetera\Widget\Traits\Material;
	
	public $statusText = '';
	public $errorText = '';

	protected function initParams()
	{
		$this->_params = array(
			'material'       => 0,
			'catalog'        => 0,
			'material_type'  => 0,
			'material_id'    => 0,
			'material_alias' => null,
			'publish'        => 1,
			'ajax'           => false,
			'redirect'       => false,
			'rating'         => true,
			'rating_text'    => $this->t->_('Оцените материал'),
			'submit_text'    => $this->t->_('Отправить сообщение'),
			'success_text'   => $this->t->_('Ваш комментарий принят'),
			'template'       => 'default.twig',
			
			'recaptcha'		       => false,
			'recaptcha_site_key'   => null,
			'recaptcha_secret_key' => null,
		);  		
	}	
		
	public function getHiddenFields()
	{
		$str  = '<input type="hidden" name="comment-add" value="'.$this->getUniqueId().'" />';
		return $str;
	}
	
	public function showNicknameInput()
	{
		$user = $this->application->getUser();
		if ($user) return false;
		return true;
	}
	
	public function showRecaptcha()
	{
		return $this->getParam('recaptcha') && !$this->getParam('ajax') && $this->getParam('recaptcha_site_key') && $this->getParam('recaptcha_secret_key');
	}
	
	protected function init()
	{
		if ( $this->showRecaptcha() ) {
			$this->application->addScript('https://www.google.com/recaptcha/api.js');
		}
		
		if (isset($_COOKIE['comments-status'])) {
			$this->statusText = $_COOKIE['comments-status'];
			setcookie('comments-status', false);
		}
		
		if ( isset($_REQUEST['comment-add']) && $_REQUEST['comment-add'] == $this->getUniqueId() ) {
			try {	
				$material = $this->getMaterial(false);
				if (!$material) throw new \Exception($this->t->_('Не указан материал'));
				
				if ( $this->showRecaptcha() ) {
					$client = new \GuzzleHttp\Client();
					$response = $client->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
						'form_params' => [
							'secret'   => $this->getParam('recaptcha_secret_key'),
							'response' => $_REQUEST['g-recaptcha-response'],
							'remoteip' => $_SERVER['REMOTE_ADDR'],
						]
					]);	
					$res = json_decode($response->getBody(), true);
					if (!$res['success']) {
						throw new \Exception($this->t->_('Проверка не пройдена'));
					}
				}
				
				$rating = null;
				if ( $this->getParam('rating') && isset( $_REQUEST['rating'] ) ) {
					$rating = (int)$_REQUEST['rating'];
				}
				
				if (!$_REQUEST['text']) throw new \Exception($this->t->_('Пустой текст'));
				
				$material->addComment(nl2br(htmlspecialchars($_REQUEST['text'])), $this->getParam('publish'), $this->application->getUser(), $_REQUEST['nickname'], $rating);
				$this->statusText = $this->getParam('success_text');
				
				if (!$this->getParam('ajaxCall') && $this->getParam('redirect')) {
					setcookie('comments-status', $this->statusText);
					header('Location: '.$this->getParam('redirect'));
					die();
				}
			} 
			catch (\Exception $e) {
				$this->errorText = $e->getMessage();
			}
		}		
	}
	
	protected function _getHtml()
	{

		$material = $this->getMaterial(false);	
	
		if ($material) {
			$this->setParam('material_type', $material->objectDefinition->id);
			$this->setParam('material_id',   $material->id);
		}
		$this->setParam('material',null);		
		$this->setParam('catalog',null);
		$this->setParam('material_alias',null);					
		
		return parent::_getHtml();
	}		
}