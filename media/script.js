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
var messages = {};
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

function getMessages ( ) {
	api({'action': 'getmessages'},
		getMessagesFinish
	);
}

function getMessagesFinish ( data ) {
	for ( message in data['messages'] ) {
		messages[message] = data['messages'][message];
	}
}

function jsMsg ( msg ) {
	if ( !messages[msg] )
		return '<' + msg + '>';
	tmp = messages[msg];
	for ( argi in arguments ) {
		if ( argi == 0 )
			continue;
		arg = arguments[argi];
		tmp = tmp.replace('$' + argi, arg);
	}
	return tmp;
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
			getUserEpisodes();
		} else {
			submitRanking(where.parentNode, id, ranking);
		}
	}
}

function handleError ( data ) {
	if ( data['status'] == 1 ) {
		alert(data['error']);
		return true;
	}
	return false;
}

function submitRankingFinish ( data ) {
	if ( handleError(data) )
		return;
	id = data['id'];
	ranking = data['ranking'];
	where = document.getElementById('episode-'+id).getElementsByTagName('div')[0];
			getUserEpisodes();
}

function submitRanking ( where, id, ranking ) {
	api({'action': 'setranking',
		'id': id,
		'ranking': ranking},
		submitRankingFinish);
}

function colourCode ( where ) {
	what = $(where).attr('id').split('-')[1];
	performColourCode(what);
}

function colourLegend ( what ) {
	if ( !$('#colourlegend').attr('id') ) {
		$('#listsortbox').append($(document.createElement('div'))
			.attr({'id': 'colourlegend'})
			.append($(document.createElement('h3'))
				.text(jsMsg('colourcode-legend'))
			).append($(document.createElement('div'))
				.attr({'id': 'colourlegend-content'})
			)
		);
	}
	
	$('#colourlegend-content').empty();
	
	if ( what == 'seasons' ) {
		for ( season in seasonColours ) {
			$('#colourlegend-content')
				.append($(document.createElement('div'))
				.css({'background-color': seasonColours[season]['full']})
				.text(jsMsg('season', season))
			);
		}
	} else if ( what == 'runs' ) {
		t = {'original': 1, 'films': 5, 'new': 6};
		for ( r in t ) {
			$('#colourlegend-content')
				.append($(document.createElement('div'))
				.css({'background-color': seasonColours[t[r]]['full']})
				.text(jsMsg('run-'+r))
			);
		}
	}
}

function performColourCode ( what ) {
	switch ( what ) {
		case 'none':
			$('#ranked .episode, #unranked .episode').each(function(i) {
				$(this).css({'background-color': ''});
				$(this).children('.episode-ranking').css({'background-color': ''});
				$(this).children('.episode-rating').css({'background-color': ''});
			});
			colourLegend(null);
			break;
		case 'seasons':
			$('#ranked .episode, #unranked .episode').each(function(i) {
				id = $(this).attr('id').split('-')[1];
				$(this).css({'background-color': seasonColours[$('#episode-'+id+'-season').val()]['full']});
				$(this).children('.episode-ranking').css({'background-color': seasonColours[$('#episode-'+id+'-season').val()]['ranking']});
				$(this).children('.episode-rating').css({'background-color': seasonColours[$('#episode-'+id+'-season').val()]['rating']});
			});
			colourLegend(what);
			break;
		case 'runs':
			$('#ranked .episode, #unranked .episode').each(function(i) {
				id = $(this).attr('id').split('-')[1];
				$(this).css({'background-color': runColours[$('#episode-'+id+'-season').val()]['full']});
				$(this).children('.episode-ranking').css({'background-color': runColours[$('#episode-'+id+'-season').val()]['ranking']});
				$(this).children('.episode-rating').css({'background-color': runColours[$('#episode-'+id+'-season').val()]['rating']});
			});
			colourLegend(what);
			break;
	}
	
}

function clean ( el ) {
	while ( el.firstChild )
		el.removeChild(el.firstChild);
}

function listSort ( which ) {
	if ( $("#ranked").hasClass('fulllist') )
		getAllEpisodes();
	if ( $("#ranked").hasClass('userlist') )
		getUserEpisodes();
}

function formatRanking ( ranking, avgranking ) {
	if ( ranking == null )
		return '?';
	ranking = Math.round(ranking);
	if ( avgranking!=null ) {
		avgranking = Math.round(avgranking*10)/10;
		return ranking + '<br /><span class="average">' + avgranking + '</span>';
	}
	return ranking;
}

function getUserEpisodes ( ) {
	order = 'asc';
	if ( $('#listsort-desc').is(':checked') )
		order = 'desc';
	rquery = {'action': 'getuserepisodes',
		'order': order,
		'type': 'ranked'};
	uquery = {'action': 'getuserepisodes',
		'order': order,
		'type': 'unranked'};
	if ( $('#list-userid') != null ) {
		userid = $('#list-userid').val();
		rquery['userid'] = userid;
		uquery['userid'] = userid;
	}
	api(rquery,
		getUserEpisodesRankedFinish
	);
	if ( $('#unranked') != null ) {
		api(uquery,
			getUserEpisodesUnrankedFinish
		);
	}
}

function getUserEpisodesRankedFinish ( data ) {
	$("#ranked").empty();
	l = $("#ranked");
	episodes = data['episodes'];
	placeEpisodes(l, episodes, true);
}

function getUserEpisodesUnrankedFinish ( data ) {
	$("#unranked").empty();
	l = $("#unranked");
	episodes = data['episodes'];
	placeEpisodes(l, episodes, true);
}

function getAllEpisodes ( ) {
	order = 'asc';
	if ( $('#listsort-desc').is(':checked') )
		order = 'desc';
	api({'action': 'getepisodes',
		'order': order},
		getAllEpisodesFinish
	);
}

function getAllEpisodesFinish ( data ) {
	$("#ranked").empty();
	l = $("#ranked");
	episodes = data['episodes'];
	placeEpisodes(l, episodes, false);
}

function placeEpisodes ( l, episodes, canalter ) {
	for ( id in episodes ) {
		l.append(
			$(document.createElement('div')).attr({
				'class': 'episode',
				'id': 'episode-'+episodes[id]['id']
			}).append($(document.createElement('input')).attr({
				'type': 'hidden',
				'id': 'episode-'+episodes[id]['id']+'-season',
				'value': episodes[id]['season']
			})).append($(document.createElement('input')).attr({
				'type': 'hidden',
				'id': 'episode-'+episodes[id]['id']+'-seasonnumber',
				'value': episodes[id]['seasonnumber']
			})).append($(document.createElement('div')).attr({
				'class': 'episode-ranking'
			}).html(formatRanking(episodes[id]['ranking'], episodes[id]['avgranking'])))
			.append($(document.createElement('div')).attr({
				'class': 'episode-title'
			}).text(episodes[id]['name']))
			.append($(document.createElement('div')).attr({
				'class': 'episode-rating'
			}).text('?'))
		);
		if ( canalter ) {
			$('#episode-'+episodes[id]['id']).children('.episode-ranking').attr('onclick', 'alterRanking(this, '+episodes[id]['id']+');');
		}
	}
	if ( $('#colourcode-seasons').is(':checked') ) {
		performColourCode('seasons');
	} else if ( $('#colourcode-runs').is(':checked') ) {
		performColourCode('runs');
	}
}

function init ( ) {
	getMessages();
	if ( $("#ranked").hasClass('fulllist') ) {
		getAllEpisodes();
	}
	if ( $("#ranked").hasClass('userlist') ) {
		getUserEpisodes();
	}
}

$.ready = init;
