var app = new Vue({
    el: '#dover',
    data: {
        dbResponse: [],
        dataTemp: [],
        dataDisplay: [],
        viewCount: 0,
        userMatch: false,
        mostTraded: '--',
        ranking: '--',
        userSearchText: '',
        countdown: 60,
        countdownText: 'Next update in 60 seconds'
    },
    methods: {
    	getLedger: vueGetFullLedger,
    	filterByUser: vueFilterByUser,
    	loadMore: vueLoadMore,
    	clickUsernameHandler: vueClickUsernameHandler
    }
});

function vueGetFullLedger(){

	$.getJSON('https://joeltersigni.com/wssimbot/api/', function (data) {
    	app.dbResponse = data;
    	app.dataTemp = data;
    	
    	//reset
		app.viewCount = 0;
		app.dataDisplay = [];
		//paginate
		vueLoadMore();
	});
}

function vueFilterByUser(username, event) {

	//get fresh results from the db if user backspaces all the way
	if(!app.userSearchText){
		vueGetFullLedger();
	}

	//get previous results before the filter if user hits backspace
	if(event.keyCode === 8 && app.userSearchText){
		app.dbResponse = app.dataTemp;
	}

	//filter results by the user search
	app.dbResponse = app.dbResponse.filter(function(transaction) {
		return transaction.username.toLowerCase().indexOf(username.toLowerCase()) > -1;
	});

	//check if a complete username has been typed in order to display stats of that specific user
	if(app.dbResponse.length > 0){
		matchUser(app.dbResponse[0].username, username);
	} else {
		app.userMatch = false;
	}

	//reset
	app.viewCount = 0;
	app.dataDisplay = [];
	//paginate
	vueLoadMore();
}

function vueLoadMore(){
	
	//amount of results
	const amount = 20;
	var nextLength = amount + app.viewCount;

	app.dataDisplay = app.dataDisplay.concat(app.dbResponse.slice(app.viewCount, nextLength));
	app.viewCount += amount;
}

function vueClickUsernameHandler(username){
	setInputBox(username);
	vueFilterByUser(username, []);
}

function matchUser(displayText, searchText){

	if(displayText.toLowerCase() === searchText.toLowerCase()){
		app.userMatch = true;
		getRanking(app.dbResponse[0].tg_id);
		getMostTraded(app.dbResponse[0].tg_id);
	} else {
		app.userMatch = false;
	}
}

function setInputBox(username){
	app.userSearchText = username;
	//$('.enterUsername').select();
}

function getRanking(tg_id){
	$.getJSON('https://joeltersigni.com/wssimbot/api/?tg_id=' + tg_id + '&position=true', function (data) {
		if(data.position){
			app.ranking = data.position;
		} else {
			app.ranking = "--";
		}
	});
}

function getMostTraded(tg_id){
	$.getJSON('https://joeltersigni.com/wssimbot/api/?tg_id=' + tg_id + '&mosttraded=true', function (data) {
		if(data.mostTraded){
			app.mostTraded = data.mostTraded;
		} else {
			app.mostTraded = "--";
		}
	});
}

vueGetFullLedger();
var seconds = setInterval(function(){
				if(!app.userSearchText && app.viewCount == 20) {
					if(app.countdown != 1){
						app.countdown = app.countdown - 1;
						app.countdownText = 'Next update in ' + app.countdown + ' seconds';
					} else {
						app.countdownText = 'Refreshing...';
						vueGetFullLedger();
						app.countdown = 60;
					}
				} else {
					app.countdownText = 'Updates paused';
					clearInterval(seconds);
				}
			}, 1000);



var leaderboards = new Vue({
    el: '#overlayarea',
    data: {
        ldrResponse: []
    },
    methods: {
    	displayOrdinal: vueDisplayOrdinal
    }
});

var menu = new Vue({
    el: '#menuarea',
    data: {
       
    },
    methods: {
    	getLeaderboard: vueGetLeaderboard,
    	clickLeaderboardHandler: vueClickLeaderboardHandler
    }
});

function vueClickLeaderboardHandler(){
	vueGetLeaderboard();
	$('.overlay').fadeIn();
}

function vueGetLeaderboard(){
	$.getJSON('https://joeltersigni.com/wssimbot/api/?leaderboard=true', function (data) {
		if(data){
			leaderboards.ldrResponse = data;
		}
	});
}

function vueDisplayOrdinal(i) {
    var j = i % 10,
        k = i % 100;
    if (j == 1 && k != 11) {
        return i + "st";
    }
    if (j == 2 && k != 12) {
        return i + "nd";
    }
    if (j == 3 && k != 13) {
        return i + "rd";
    }
    return i + "th";
}

$('.overlayclose').click(function(){
	$('.overlay').fadeOut();
});
