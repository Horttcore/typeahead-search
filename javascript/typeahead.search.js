var TypeaheadSearchPlugin;

jQuery(document).ready(function(){

	var Plugin = {

		init:function(){

			// Cache
			Plugin.search = jQuery('input[name="s"]');

			// Go
			Plugin.bindings();

		},

		bindings:function(){

			Plugin.Bloodhound = new Bloodhound({
				name: 'typeahead-search',
				datumTokenizer: Bloodhound.tokenizers.obj.whitespace('post_title'),
				queryTokenizer: Bloodhound.tokenizers.whitespace,
				limit: 10,
				prefetch: {
					url: TypeaheadSearch.prefetchUrl
				}
			});

			Plugin.Bloodhound.initialize();

			Plugin.search.typeahead({
				highlight: true
			},{
				name: 'search',
				displayKey: 'post_title',
				source: Plugin.Bloodhound.ttAdapter()
			}).bind('typeahead:selected', function(event, datum, name ){
				Plugin.search.parents('form:first').submit();
				/*
				if ( datum.permalink )
					window.location.href = datum.permalink;
				*/
			});

		}

	}

	TypeaheadSearchPlugin = Plugin;
	TypeaheadSearchPlugin.init();

});
