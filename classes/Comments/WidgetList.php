<?php
namespace Comments;

class WidgetList extends \Cetera\Widget\Templateable
{
	use \Cetera\Widget\Traits\Material;
	use \Cetera\Widget\Traits\Paginator;
	
	protected $_comments = null;
	protected $_form = null;
			
	protected function initParams()
	{
		$this->_params = array(
			'template'       => 'default.twig',
			'material'       => 0,
			'catalog'        => 0,
			'material_type'  => 0,
			'material_id'    => 0,
			'material_alias' => null,
			'ajax'           => false,
			'order'              => 'dat',
			'sort'               => 'DESC',		
			'limit'              => 10,
			'page'               => null,
			'page_param'         => 'page',
			'paginator'          => true,
			'paginator_url'      => '?{query_string}',
			'paginator_template' => false,
			'rating' 			 => false,
			
			'form'               => true,
			'form_template'      => null,
			'form_redirect'      => false,
			'form_publish'       => 1,	
			'form_rating_text'   => $this->t->_('Оцените материал'),		
			'form_title'         => '<h3>'.$this->t->_('Добавить комментарий').'</h3>',
			'form_submit_text'   => $this->t->_('Отправить сообщение'),
			'form_success_text'  => $this->t->_('Ваш комментарий принят'),		
			'form_recaptcha'		    => false,
			'form_recaptcha_site_key'   => null,
			'form_recaptcha_secret_key' => null,
		);  		
	}	
	
	public function getWidgetTitle()
	{
		return str_replace('{count}', $this->getComments()->getCountAll(), $this->widgetTitle);
	}
	
	public function getChildren()
	{
		return $this->getComments();
	}
	
	public function getComments()
	{
		if (!$this->_comments)
		{
			$m = $this->getMaterial(false);
			if ($m)
			{
				$this->_comments = $m->getComments();
			}
			else
			{
				$this->_comments = \Comments\Plugin::getCommentsObjectDefinition()->getMaterials();
			}
			$this->_comments->orderBy($this->getParam('order'), $this->getParam('sort'), false);
			if ($this->getParam('limit')) $this->_comments->setItemCountPerPage($this->getParam('limit')); 
			$this->_comments->setCurrentPageNumber( $this->getPage() ); 
		}
		return $this->_comments;		
	}
	
    public function getForm()
    {
		if ($this->_form === null) 
		{
			if (!$this->getParam('form'))
			{
				$this->_form = '';
			} 
			else
			{
				$this->_form = $this->application->getWidget('Comments.Add',array(
					'material'       => $this->getParam('material'),
					'material_id'    => $this->getParam('material_id'),
					'material_type'  => $this->getParam('material_type'),
					'material_alias' => $this->getParam('material_alias'),
					'catalog'        => $this->getParam('catalog'),
					'ajax'        => $this->getParam('ajax'),
					'widgetTitle' => $this->getParam('form_title'),
					'rating'      => $this->getParam('rating'),
					'redirect'    => $this->getParam('form_redirect'),	
					'publish'     => $this->getParam('form_publish'),	
					'rating_text' => $this->getParam('form_rating_text'),					
					'submit_text' => $this->getParam('form_submit_text'),
					'success_text'=> $this->getParam('form_success_text'),
					'recaptcha'            => $this->getParam('form_recaptcha'),					
					'recaptcha_site_key'   => $this->getParam('form_recaptcha_site_key'),
					'recaptcha_secret_key' => $this->getParam('form_recaptcha_secret_key'),					
					'template'    => $this->getParam('form_template')?$this->getParam('form_template'):'default.twig',
				))->getHtml();
			}
		}	
		return $this->_form;
	}	
		
	protected function _getHtml()
	{
		$this->getForm();
		
		$material = $this->getMaterial(false);
		if ($material)
		{
			$this->setParam('material_type', $material->objectDefinition->id);
			$this->setParam('material_id',   $material->id);
		}
		$this->setParam('material',null);		
		$this->setParam('catalog',null);
		$this->setParam('material_alias',null);		

		return parent::_getHtml();
	}	
}