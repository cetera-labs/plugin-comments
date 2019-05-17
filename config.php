<?php
$t = $this->getTranslator();
$t->addTranslation(__DIR__.'/lang');

try
{
	$od = \Cetera\ObjectDefinition::findByAlias( 'comments' );
	$od->registerClass( $od->id , '\Comments\Comment' );

	\Cetera\Material::addPlugin( '\Comments\Plugin' );

	$this->registerWidget(array(
		'name'          => 'Comments.Add',
		'class'         => '\\Comments\\WidgetAdd',
		'not_placeable' => true
	));

	$this->registerWidget(array(
		'name'    => 'Comments.List',
		'class'   => '\\Comments\\WidgetList',
		'describ' => $t->_('Комментарии'),
		'icon'    => 'images/icon.png',
		'ui'      => 'Plugin.comments.Widget',
	));

	define('GROUP_COMMENTS', -103);

	$this->addUserGroup(array(
		'id'      => GROUP_COMMENTS,
		'name'    => $t->_('Модераторы комментариев'),
		'describ' => '',
	));

	if ($this->getBo() && $this->getUser() && $this->getUser()->hasRight(GROUP_COMMENTS) )
	{
		$this->getBo()->addModule(array(
				'id'	   => 'comments',
				'position' => MENU_SITE,
				'name' 	   => $t->_('Комментарии'),
				'icon'     => 'images/icon.png',
				'class'    => 'Plugin.comments.Panel'
		));

	}

}
catch (\Exception $e) {}