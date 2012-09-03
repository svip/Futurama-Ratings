var episodes = {};
var apiUrl = './ajax.php';
var seasonColours = {
	1: {
		'full': '#ec7da4',
		'ranking': '#e8457f',
		'rating': '#ed6796'
	},
	2: {
		'full': '#e57dec',
		'ranking': '#cf15db',
		'rating': '#f55aff'
	},
	3: {
		'full': '#9879e5',
		'ranking': '#5219e3',
		'rating': '#8e62fd'
	},
	4: {
		'full': '#89bbdb',
		'ranking': '#198ad2',
		'rating': '#61c2ff'
	},
	5: {
		'full': '#91deb3',
		'ranking': '#15d46a',
		'rating': '#65f6a6'
	},
	6: {
		'full': '#abde89',
		'ranking': '#62cd1b',
		'rating': '#9ef962'
	},
	7: {
		'full': '#d4d781',
		'ranking': '#abb10f',
		'rating': '#eef44e'
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
		'full': '#91deb3',
		'ranking': '#15d46a',
		'rating': '#65f6a6'
	},
	6: {
		'full': '#abde89',
		'ranking': '#62cd1b',
		'rating': '#9ef962'
	},
	7: {
		'full': '#abde89',
		'ranking': '#62cd1b',
		'rating': '#9ef962'
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

function alterRating ( where, id ) {
	currentRating = $(where).text();
	if ( currentRating == '?' )
		currentRating = '';
	$(where).empty();
	
	input = $(document.createElement('input'))
		.attr({'size': '3',
		'id': 'episode-'+id+'-rating',
		'value': currentRating});
	$(where).append(input).bind('click', null);
	
	input.focus();
	input.attr('onkeydown', 'checkRatingSubmit(event, this, '+id+');');
}

function submitRatingFinish ( data ) {
	if ( handleError(data) )
		return;
	getUserEpisodes();
}

function submitRating ( where, id, rating ) {
	api({'action': 'setrating',
		'id': id,
		'rating': rating},
		submitRatingFinish);
}

function checkRatingSubmit ( event, where, id ) {
	if ( event.keyCode == 13 ) {
		rating = parseFloat($(where).val());
		if ( rating == 0 ) {
			getUserEpisodes();
		} else {
			submitRating(where.parentNode, id, rating);
		}
	}
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

function submitRankingFinish ( data ) {
	if ( handleError(data) )
		return;
	getUserEpisodes();
}

function submitRanking ( where, id, ranking ) {
	api({'action': 'setranking',
		'id': id,
		'ranking': ranking},
		submitRankingFinish);
}

function handleError ( data ) {
	if ( data['status'] == 1 ) {
		alert(data['error']);
		return true;
	}
	return false;
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

function formatRating ( rating, avgrating ) {
	if ( rating != null )
		return Math.round(rating*10)/10;
	if ( avgrating != null )
		return Math.round(avgrating*10)/10;
	return '?';
}

function getUserEpisodes ( ) {
	order = 'asc';
	sortby = 'ranking';
	if ( $('#listsort-ranking-desc').is(':checked') ) {
		order = 'desc';
	}
	if ( $('#listsort-rating-asc').is(':checked') ) {
		sortby = 'rating';
	}
	if ( $('#listsort-rating-desc').is(':checked') ) {
		order = 'desc';
		sortby = 'rating';
	}
	rquery = {'action': 'getuserepisodes',
		'order': order,
		'sortby': sortby,
		'type': 'ranked'};
	uquery = {'action': 'getuserepisodes',
		'order': order,
		'sortby': sortby,
		'type': 'unranked'};
	if ( $('#list-userid').val() != undefined ) {
		userid = $('#list-userid').val();
		rquery['userid'] = userid;
		uquery['userid'] = userid;
	}
	api(rquery,
		getUserEpisodesRankedFinish
	);
	if ( $('#unranked').attr('id') != undefined ) {
		api(uquery,
			getUserEpisodesUnrankedFinish
		);
	}
}

function getUserEpisodesRankedFinish ( data ) {
	$("#ranked").empty();
	l = $("#ranked");
	episodes = data['episodes'];
	if ( $('#list-userid').val() != undefined ) {
		placeEpisodes(l, episodes, false);
	} else {
		placeEpisodes(l, episodes, true);
	}
}

function getUserEpisodesUnrankedFinish ( data ) {
	$("#unranked").empty();
	l = $("#unranked");
	episodes = data['episodes'];
	placeEpisodes(l, episodes, true);
}

function getAllEpisodes ( ) {
	order = 'asc';
	sortby = 'ranking';
	if ( $('#listsort-ranking-desc').is(':checked') ) {
		order = 'desc';
	}
	if ( $('#listsort-rating-asc').is(':checked') ) {
		sortby = 'rating';
	}
	if ( $('#listsort-rating-desc').is(':checked') ) {
		order = 'desc';
		sortby = 'rating';
	}
	api({'action': 'getepisodes',
		'order': order,
		'sortby': sortby},
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
			}).text(formatRating(episodes[id]['rating'], episodes[id]['avgrating'])))
		);
		if ( canalter ) {
			$('#episode-'+episodes[id]['id']).children('.episode-ranking').attr('onclick', 'alterRanking(this, '+episodes[id]['id']+');');
			$('#episode-'+episodes[id]['id']).children('.episode-rating').attr('onclick', 'alterRating(this, '+episodes[id]['id']+');');
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
