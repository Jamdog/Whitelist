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
	$('.activate a').click(function(e) {
		e.preventDefault();
		//toggle player active state ajax
		var playerid = table.rows( { selected: true } ).data()[0][0];
		var rootPage = window.location.href;
		var n = rootPage.indexOf('players/');
		rootPage = rootPage.substring(0, n != -1 ? n : rootPage.length);
		if ($('tr[data-playerid='+playerid+'] .activeCell').html() != '') {
			$.post(rootPage+'/ajax/activate.php',{uid:playerid,active:0}).done(function() {
				$('tr[data-playerid='+playerid+'] .activeCell').html('');
			});
		} else {
			$.post(rootPage+'/ajax/activate.php',{uid:playerid,active:1}).done(function() {
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
	$('.messageSubmit').click(function() {
		var rootPage = window.location.href;
		var n = rootPage.indexOf('players/');
		var memId = $(this).attr('data-memid');
		rootPage = rootPage.substring(0, n != -1 ? n : rootPage.length);
		$(".warningList tbody tr").each(function() {
			messageId = $(this).attr('data-row');
			warning = $(this).find('.warning').html().trim();
			severity = $(this).find('.severity').html().trim();
			orgDate = $(this).find('.orgDate span').html().trim();
			if (severity == '') {
				severity = '0';
			}
			severity = parseInt(severity);
			if (!(warning == '' && messageId == 'new')) {
				$.post(rootPage+'/ajax/message.php',{id:messageId,w:warning,s:severity,mid:memId,org:orgDate},'json').done(function(data) {
					data = $.parseJSON(data);
					if (data.newid !== '') {
						$('.warningList tr[data-row=new]').attr('data-row',data.newid);
						var today = new Date();
						var dd = today.getDate();
						var mm = today.getMonth()+1; //January is 0!
						var yyyy = today.getFullYear();
						$('.warningList tbody').append("<tr data-row='new'><td class='date orgDate'><span>"+yyyy+"-"+mm+"-"+dd+"</span><input type='text' value='"+yyyy+"-"+mm+"-"+dd+"' /></td><td class='date'></td><td class='warning' contenteditable></td><td class='severity' contenteditable></td></tr>");
						$('tr[data-row=new] .orgDate input').datepicker({dateFormat: 'yy-mm-dd'});
					}
				});
				if (warning == '' && messageId != 'new') {
					$('tr[data-row='+messageId+"]").remove();
				}
			}
		});
	});
	$('.orgDate input').each(function() {
		$(this).datepicker({
			dateFormat: 'yy-mm-dd'
		});
	});
	$('.warningList').on('click','.orgDate', function() {
		$(this).find("input").datepicker('show');
	});
	$('.warningList').on('change','.orgDate input', function() {
		$(this).parent().find('span').html($(this).val());
	});	
});
function groupChange(users,groupid,action) {
	var rootPage = window.location.href;
	var n = rootPage.indexOf('players/');
	rootPage = rootPage.substring(0, n != -1 ? n : rootPage.length);
	users.each(function(data) {
		console.log("uid: "+data[0]+" gid: "+groupid+" "+action);
		if (action == 'add') {
			$.post(rootPage+'/ajax/group.php',{uid:data[0],group:groupid,add:1}).done(function() {
				$('tr[data-playerid='+data[0]+'] td[data-groupid='+groupid+']').html('<i class="fa fa-check"></i>');
			});
		} else {
			$.post(rootPage+'/ajax/group.php',{uid:data[0],group:groupid,add:0}).done(function() {
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
		if ($('tr[data-playerid='+userInfo[0]+'] .activeCell').html() != '') {
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