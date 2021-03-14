<?php
$t = $this->getTranslator();
$t->addTranslation(__DIR__.'/lang');

try {
    $od = null;
	$od = \Cetera\ObjectDefinition::findByAlias( 'comments' );
}
catch (\Exception $e) {}

if ($od) {
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
		'icon'    => '/cms/plugins/comments/images/icon.png',
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
				'icon'     => '/cms/plugins/comments/images/icon.png',
                'iconCls'  => 'x-fa fa-comments',
				'class'    => 'Plugin.comments.Panel'
		));

	}
}