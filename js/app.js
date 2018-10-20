var app = new Vue({
    el: '#dover',
    data: {
        dbResponse: [],
        dataTemp: [],
        dataDisplay: [],
        viewCount: 0,
        userSearchText: ''
    },
    methods: {
    	getLedger: vueGetFullLedger,
    	filterByUser: vueFilterByUser,
    	loadMore: vueLoadMore
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

	app.dbResponse = app.dbResponse.filter(function(transaction) {
		return transaction.username.toLowerCase().indexOf(username.toLowerCase()) > -1;
	});

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

vueGetFullLedger();

//refresh every 60 seconds
/*
setInterval(function(){
	if(!app.userSearchText){
		vueGetFullLedger();
		console.log("Refreshed");
	} else {
		console.log("Refresh Cancelled");
	}
}, 60000);*/