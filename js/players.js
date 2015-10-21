$(document).ready(function() {
	var table = $('.players').DataTable({
		select: true,
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
		var rowData = table.rows( { selected: true } ).data().toArray();
		var userInfo = rowData[0];
		var count = table.rows( { selected: true } ).count();
		contextMenu(count,userInfo);
	});
	$('.activate a').click(function() {
		//toggle player active state ajax
		var playerid = table.rows( { selected: true } ).data()[0][0];
		if ($('tr[data-playerid='+playerid+'] .activeCell').html() != '') {
			$.post('./ajax/activate.php',{uid:playerid,active:0}).done(function() {
				$('tr[data-playerid='+playerid+'] .activeCell').html('');
			});
		} else {
			$.post('../ajax/activate.php',{uid:playerid,active:1}).done(function() {
				$('tr[data-playerid='+playerid+'] .activeCell').html('<i class="fa fa-check"></i>');
			});
		}
	});
	$('.addGroup select').change(function() {
		groupChange(table.rows( { selected: true } ).data(),$(this).val(),'add');
		$(this).val('dud');
	});
	$('.removeGroup select').change(function() {
		groupChange(table.rows( { selected: true } ).data(),$(this).val(),'remove');
		$(this).val('dud');

	});
});
function groupChange(users,groupid,action) {
	users.each(function(data) {
		console.log("uid: "+data[0]+" gid: "+groupid+" "+action);
		if (action == 'add') {
			$.post('../ajax/group.php',{uid:data[0],group:groupid,add:1}).done(function() {
				$('tr[data-playerid='+data[0]+'] td[data-groupid='+groupid+']').html('<i class="fa fa-check"></i>');
			});
		} else {
			$.post('../ajax/group.php',{uid:data[0],group:groupid,add:0}).done(function() {
				$('tr[data-playerid='+data[0]+'] td[data-groupid='+groupid+']').html('');
			});
		}
	});
}
function contextMenu(count,userInfo) {
	$('.playersMenu li').hide();
	if (count == 1) {
		//one player selected
		$('.singleSelect').show();
		if (userInfo[4] != '') {
			var activeContext = "Deactivate";
		} else {
			var activeContext = "Activate";
		}
		$('.activate a').html(activeContext);
		var rootPage = window.location.href;
		var n = rootPage.indexOf('players/');
		rootPage = rootPage.substring(0, n != -1 ? n : rootPage.length);
		$('.viewUser a').attr('href',rootPage+"players/"+userInfo[1]+"/");
	} else if (count > 1) {
		//multiple players selected
		$('.multiSelect').show();
	} else {
		$('.noSelect').show();
	}
}