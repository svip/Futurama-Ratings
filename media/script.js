var episodes = {};
var apiUrl = './ajax.php';
var seasonColours = {
	1: {
		'full': '#ec7da4',
		'ranking': '#e8457f',
		'rating': '#ed6796'
	},
	2: {
		'full': '#d480bd',
		'ranking': '#ea4bbf',
		'rating': '#f174cf'
	},
	3: {
		'full': '#ce94ec',
		'ranking': '#ce77fb',
		'rating': '#a33fd7'
	},
	4: {
		'full': '#9f99dc',
		'ranking': '#7e72f3',
		'rating': '#5445e0'
	},
	5: {
		'full': '#8aadd5',
		'ranking': '#317cd0',
		'rating': '#6eabef'
	},
	6: {
		'full': '#84cacb',
		'ranking': '#3bcdcf',
		'rating': '#6ce4e5'
	},
	7: {
		'full': '#78d7ab',
		'ranking': '#32db8d',
		'rating': '#60f8b2'
	}
};
var runColours = {
	1: {
		'full': '#ec7da4',
		'ranking': '#e8457f',
		'rating': '#ed6796'
	},
	2: {
		'full': '#ec7da4',
		'ranking': '#e8457f',
		'rating': '#ed6796'
	},
	3: {
		'full': '#ec7da4',
		'ranking': '#e8457f',
		'rating': '#ed6796'
	},
	4: {
		'full': '#ec7da4',
		'ranking': '#e8457f',
		'rating': '#ed6796'
	},
	5: {
		'full': '#8aadd5',
		'ranking': '#317cd0',
		'rating': '#6eabef'
	},
	6: {
		'full': '#84cacb',
		'ranking': '#3bcdcf',
		'rating': '#6ce4e5'
	},
	7: {
		'full': '#84cacb',
		'ranking': '#3bcdcf',
		'rating': '#6ce4e5'
	}
};
/*var tabindex = 1;
var userid = 0;*/

function api ( query, callback ) {
	$.ajax({
		url: apiUrl,
		type: 'POST',
		data: query,
		dataType: 'json',
		success: function ( data ) {
			callback ( data );
		}
	});
}

function alterRanking ( where, id ) {
	currentRanking = where.firstChild.data;
	if ( currentRanking == '?' )
		currentRanking = '';
	clean(where);
	
	input = document.createElement('input');
	input.setAttribute('size', '3');
	input.setAttribute('id', 'episode-'+id+'-ranking');
	input.setAttribute('value', currentRanking);
	where.appendChild(input);
	
	where.setAttribute('onclick', '');
	
	input.focus();
	input.setAttribute('onkeydown', 'checkRankingSubmit(event, this, '+id+');');
}

function checkRankingSubmit ( event, where, id ) {
	if ( event.keyCode == 13 ) {
		ranking = parseInt(where.value);
		if ( ranking == 0 ) {
			endAlterRanking(where.parentNode, id, 0);
		} else {
			submitRanking(where.parentNode, id, ranking);
		}
	}
}

function handleError ( data ) {
	if ( data['status'] == 1 )
		alert(data['error']);
}

function submitRankingFinish ( data ) {
	id = data['id'];
	ranking = data['ranking'];
	where = document.getElementById('episode-'+id).getElementsByTagName('div')[0];
	endAlterRanking ( where, id, ranking );
}

function submitRanking ( where, id, ranking ) {
	api({'action': 'setranking',
		'id': id,
		'ranking': ranking},
		submitRankingFinish);
}

function endAlterRanking ( where, id, value ) {
	clean(where);
	if ( value == 0 ) {
		where.appendChild(document.createTextNode('?'));
	} else {
		where.appendChild(document.createTextNode(ranking));
		ep = where.parentNode;
		if ( ep.parentNode.getAttribute('id') == 'unranked' ) {
			document.getElementById('ranked').appendChild(ep);
			//document.getElementById('unranked').removeChild(ep);
		}
		where.setAttribute('onclick', 'alterRanking(this, '+id+');');
		sortRankings();
	}
}

function sortRankings ( ) {
	// not implemented
}

function colourCode ( where ) {
	what = $(where).attr('id').split('-')[1];
	switch ( what ) {
		case 'none':
			$('#ranked .episode, #unranked .episode').each(function(i) {
				$(this).css({'background-color': ''});
				$(this).children('.episode-ranking').css({'background-color': ''});
				$(this).children('.episode-rating').css({'background-color': ''});
			});
			break;
		case 'seasons':
			$('#ranked .episode, #unranked .episode').each(function(i) {
				id = $(this).attr('id').split('-')[1];
				$(this).css({'background-color': seasonColours[$('#episode-'+id+'-season').val()]['full']});
				$(this).children('.episode-ranking').css({'background-color': seasonColours[$('#episode-'+id+'-season').val()]['ranking']});
				$(this).children('.episode-rating').css({'background-color': seasonColours[$('#episode-'+id+'-season').val()]['rating']});
			});
			break;
		case 'runs':
			$('#ranked .episode, #unranked .episode').each(function(i) {
				id = $(this).attr('id').split('-')[1];
				$(this).css({'background-color': runColours[$('#episode-'+id+'-season').val()]['full']});
				$(this).children('.episode-ranking').css({'background-color': runColours[$('#episode-'+id+'-season').val()]['ranking']});
				$(this).children('.episode-rating').css({'background-color': runColours[$('#episode-'+id+'-season').val()]['rating']});
			});
			break;
	}
	
}

function clean ( el ) {
	while ( el.firstChild )
		el.removeChild(el.firstChild);
}

function init ( ) {
	
}
