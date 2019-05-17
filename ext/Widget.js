Ext.require('Cetera.field.WidgetTemplate');
Ext.require('Cetera.field.Folder');

Ext.define('Plugin.comments.Widget', {

    extend: 'Cetera.widget.Widget',
				
	formfields: [
		{
			xtype: 'hiddenfield',
			name: 'material_type',
			value: 1					
		},	
		{
			xtype: 'folderfield',
			itemId: 'material',
			name: 'material_id',
			fieldLabel: _('Материал'),
			materials: 1,
			nocatselect: 1,
			matsort: 'dat DESC',
			listeners: {
				select: function(res) {
					this.up('form').getForm().setValues({
						material_type:res.type
					});
				}
			}
		},	
		{
			xtype: 'fieldcontainer',
			cls: 'x-field',
			fieldLabel: _('Страницы'),
			layout: 'hbox',
			items: [
			{
				xtype: 'numberfield',
				name: 'limit',
				fieldLabel: _('комментариев на странице'),
				labelWidth: 200,
				maxValue: 999,
				minValue: 1,
				allowBlank: false,
				flex: 1,
				margin: '0 5 0 0',
			},{
				xtype:          'checkbox',
				boxLabel:       _('показать навигацию'),
				name:           'paginator',
				inputValue:     1,
				uncheckedValue: 0,
				flex: 1
			}]
		},
		{
			xtype: 'fieldcontainer',
			cls: 'x-field',
			layout: 'hbox',
			items: [{
				xtype: 'textfield',
				name: 'page_param',
				labelWidth: 200,
				fieldLabel: _('query параметр'),
				flex: 1,
				margin: '0 5 0 0',
			},{
				xtype: 'textfield',
				name: 'paginator_url',
				fieldLabel: _('ссылка на страницу'),
				flex: 1
			}]
		},		
		{
			xtype: 'widgettemplate',
			widget: 'Comments.List'
		}
	],			

    setParams : function(params) {
		this.callParent([params]);
		if (params.material_id && params.material_type) {
			this.form.queryById('material').setValue(params.material_id, false, params.material_type);
		}
    }	

});
