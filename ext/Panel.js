Ext.define('Plugin.comments.Panel', {

    extend:'Ext.grid.Panel',
    
    border: false,
    loadMask: true,
    stripeRows: true,   
    multiSelect: true,     
              
    columns: [
        {
            header: "", 
            width: 25, 
            sortable: false, 
            dataIndex: 'icon', 
            renderer: function (value) {
                if (value == '1')
                    return '<img src="images/globe_s.gif" title="'+Config.Lang.published+'" width="14" height="14" />';
                    else return '<img src="images/globe_c_s.gif" title="'+Config.Lang.unpublished+'" width="14" height="14" />';
            }
        },
        {header: "ID", width: 50, dataIndex: 'id'},
        {header: 'Комментарий', width: 75, dataIndex: 'comment', flex:2},
		{
			header: Config.Lang.material, width: 100, dataIndex: 'name', flex:1,
			renderer: function (value, p, record) {
                return '<a href="javascript:Config.modules[\'comments\'][\'object\'].edit('+record.get('material_id')+','+record.get('material_type')+')">'+value+'</a>';
            }
		},		
        {header: Config.Lang.date, width: 105, dataIndex: 'dat', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i')},
        {
			header: Config.Lang.author, width: 100, dataIndex: 'autor',
			renderer: function (value, p, record) {
                return '<a href="javascript:Cetera.getApplication().openBoLink(\'user:' + record.get('autor_id') + '\')">'+value+'</a>';
            }
		}
    ],
                
    edit: function(id, type) {
		
		if (!type) 
		{
			type = 'comments';
			type2 = 'Comments';
		}
		else
		{
			type2 = type;
		}
		
        if (this.editWindow) this.editWindow.destroy();
        this.editWindow = Ext.create('Cetera.window.MaterialEdit', { 
            listeners: {
                close: {
                    fn: function(win){
                        this.store.load();
                        this.stopInactivityTimer();
						win.materialForm.destroy();
                    },
                    scope: this
                }
            }
        });
        
        var win = this.editWindow;
        win.show();

        Ext.Loader.loadScript({
            url: 'include/ui_material_edit.php?type='+type+'&id='+id+'&height='+this.editWindow.height,
			scope: this,
            onLoad: function() { 
                var cc = Ext.create('MaterialEditor'+type2, {win: win});
                if (cc) { 
					         cc.show(); 
					         this.fireEvent('material_editor_ready', win, cc);
				        }
            }
        });
        
        // Таймер неактивности
        this.clearInactivityTimeout();
        this.timeoutTask = Ext.TaskManager.start({
             run: function() {
             
                if (this.globalTimeout <= 0) {
                    // таймаут сработал, останавливаем таймер
                    this.stopInactivityTimer();
                    this.editWindow.materialForm.saveAction(0,0,1);
                } else {
                    this.globalTimeout = this.globalTimeout - 1;
                }
             
             },
             interval: 1000,
             scope: this
        });

        Ext.EventManager.addListener("main_body", 'click', this.clearInactivityTimeout, this);
        Ext.EventManager.addListener("main_body", 'keypress', this.clearInactivityTimeout, this);
        
    },
    
    clearInactivityTimeout: function() {
        this.globalTimeout = 28800;
    },
    
    stopInactivityTimer: function() {
         Ext.TaskManager.stop(this.timeoutTask);
    },
      
    deleteMat: function() {
        Ext.MessageBox.confirm(Config.Lang.materialDelete, Config.Lang.r_u_sure, function(btn) {
            if (btn == 'yes') this.call('delete');
        }, this);
    },
      
    call: function(action) {
        Ext.Ajax.request({
            url: 'include/action_materials.php',
            params: { 
                action: action, 
                type: 'comments', 
                'sel[]': this.getSelected()
            },
            scope: this,
            success: function(resp) {
                this.store.load();
            }
        });
    },
       
    getSelected: function() {
        var a = this.getSelectionModel().getSelection();
        ret = [];
        for (var i=0; i<a.length; i++) ret[i] = a[i].getId();
        return ret;
    },     
    
    initComponent : function() {
               
        this.store = new Ext.data.JsonStore({
            autoDestroy: true,
			autoLoad: true,
            remoteSort: true,
            fields: ['icon','tag','name','comment',{name: 'dat', type: 'date', dateFormat: 'timestamp'},'autor','locked','autor_id','material_id','material_type'],
            totalProperty: 'total',
            pageSize: Config.defaultPageSize,
            sorters: [{property: "dat", direction: "DESC"}],
            proxy: {
                type: 'ajax',
                url: '/plugins/comments/data.php',
                simpleSortMode: true,
                reader: {
                    type: 'json',
					root: 'rows',
                    rootProperty: 'rows'
                },
                extraParams: {
                    'id': 0, 
                    limit: Config.defaultPageSize
                }
            }
        });
        
        this.bbar = Ext.create('Ext.PagingToolbar', {
            store: this.store,
            items: [Config.Lang.filter + ': ', Ext.create('Cetera.field.Search', {
                store: this.store,
                paramName: 'query',
                width:200
            })]
        });
        
        this.toolbar = Ext.create('Ext.toolbar.Toolbar', {items: [
			{	
                iconCls: 'icon-reload',
                tooltip: Config.Lang.reload,
                handler: function(btn) { btn.up('grid').getStore().load(); },
                scope: this
            },		
			'-',
            {
                itemId: 'tb_mat_edit',
                disabled: true,
                iconCls:'icon-edit',
                tooltip: Config.Lang.edit,
                handler: function () { this.edit(this.getSelectionModel().getSelection()[0].getId()); },
                scope: this
            },
            {
                itemId: 'tb_mat_delete',
                disabled: true,
                iconCls:'icon-delete',
                tooltip: Config.Lang.delete,
                handler: function () { this.deleteMat(); },
                scope: this
            },
            '-',
            {
                itemId: 'tb_mat_pub',
                disabled: true,
                iconCls:'icon-pub',
                tooltip: Config.Lang.publish,
                handler: function() { this.call('pub'); },
                scope: this
            },
            {
                itemId: 'tb_mat_unpub',
                disabled: true,
                iconCls:'icon-unpub',
                tooltip: Config.Lang.unpublish,
                handler: function() { this.call('unpub'); },
                scope: this
            }
        ]});
        
        this.tbar = this.toolbar;
         
		 
        this.on({
            'celldblclick' : function() {
                this.edit(this.getSelectionModel().getSelection()[0].getId());
            },
            scope: this
        });  
		
		this.callParent(); 
        
        this.getSelectionModel().on({
            'selectionchange' : function(sm){
				
                var hs = sm.hasSelection();
                var sf = this.store.sorters.first().property;
                this.toolbar.getComponent('tb_mat_edit').setDisabled(!hs);
                this.toolbar.getComponent('tb_mat_delete').setDisabled(!hs);
                this.toolbar.getComponent('tb_mat_pub').setDisabled(!hs);
                this.toolbar.getComponent('tb_mat_unpub').setDisabled(!hs);
			
            },
            'beforeselect' : function(t , record, index, eOpts) {
                if (record.get('locked')) return false;
            },
            scope:this
        });
       
        this.getView().getRowClass = function(record, rowIndex, rowParams, store){ 
             if (record.get('locked')) return 'disabled';
        } 
        
    }
                
});