$(document).ready(function() {
	var table = $('.players').DataTable({
		select: {
			style:'multi'
		},
		"columnDefs": [
			{
				"targets": [ 0 ],
				"visible": false,
				"searchable": false
			}
		]
	});
	table.on( 'select', function ( e, dt, type, indexes ) {
		var rowData = table.rows( indexes ).data().toArray();
		var userInfo = rowData[0];
		var count = table.rows( { selected: true } ).count();
		contextMenu(count,userInfo);
	}).on( 'deselect', function ( e, dt, type, indexes ) {
		var rowData = table.rows( indexes ).data().toArray();
		var userInfo = rowData[0];
		var count = table.rows( { selected: true } ).count();
		contextMenu(count,userInfo);
	});
});
function contextMenu(count,userInfo) {
	$('.playersMenu li').hide();
	console.log(userInfo[4]);
	if (count == 1) {
		//one player selected
		$('.singleSelect').show();
		if (userInfo[4] == 'Active') {
			var activeContext = "Deactivate";
		} else {
			var activeContext = "Activate";
		}
		$('.activate a').html(activeContext);

		$('.viewUser a').attr('href',window.location.href+userInfo[1]+"/");
	} else if (count > 1) {
		//multiple players selected
		$('.multiSelect').show();
	} else {
		$('.noSelect').show();
	}
}