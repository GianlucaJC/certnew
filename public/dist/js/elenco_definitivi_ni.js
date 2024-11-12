$(document).ready( function () {
    $('#tbl_articoli tfoot th').each(function () {
        var title = $(this).text();
		if (title.length!=0)
			$(this).html('<input type="text" placeholder="Search ' + title + '" />');
    });	
	
	
    var table=$('#tbl_articoli').DataTable({
		"fnDrawCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;
			ind_sum=0

			this.api()
			.columns({ search: 'applied' })
            .every(function () {
				
				let ourSum = 0
				if ($(this.header()).hasClass('sum')) {
					ind_sum++
					ourSum = this.data().reduce(function(a, b) {
						var x = parseFloat(a) || 0;
						var y = parseFloat(b) || 0;
						return x + y;
					}, 0);

					$('#sum_res'+ind_sum).html("<b>"+ourSum+"</b>");
				}
			})
		},			
		
		dom: 'Bfrtip',
		buttons: [
			'excel', 'pdf'
		],		
        initComplete: function () {
            // Apply the search
            this.api()
                .columns()
                .every(function () {
                    var that = this;
 
                    $('input', this.footer()).on('keyup change clear', function () {
                        if (that.search() !== this.value) {
                            that.search(this.value).draw();
                        }
                    });
                });
        },
        language: {
            lengthMenu: 'Visualizza _MENU_ records per pagina',
            zeroRecords: 'Nessun definitivo non idoneo trovato',
            info: 'Pagina _PAGE_ di _PAGES_',
            infoEmpty: 'Non sono presenti definitivi non idonei',
            infoFiltered: '(Filtrati da _MAX_ definitivi non idonei totali)',
        },

		
    });	
	
	/*
	table.columns().every(function(index, tableCounter, counter) {
		let ourSum = 0
		let column = this;
		
		if ($(column.header()).hasClass('sum')) {
		  ourSum = column.data().reduce(function(a, b) {
			var x = parseFloat(a) || 0;
			var y = parseFloat(b) || 0;
			return x + y;
		  }, 0);
		}
		else {
		  ourSum = ""
		}
		console.log("column " + index + ": ourSum " + ourSum);	
	})
	*/
    
   
	

	
} );


function dele_element(value) {
	if(!confirm('Sicuri di eliminare l\'elemento?')) 
		event.preventDefault() 
	else 
		$('#dele_contr').val(value)	
}

function restore_element(value) {
	if(!confirm('Sicuri di ripristinare l\'elemento?')) 
		event.preventDefault() 
	else 
		$('#restore_contr').val(value)	
}