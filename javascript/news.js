const generalBtn = document.getElementById('general');
const cyberBtn = document.getElementById('cyber');
const hacksBtn = document.getElementById('hacks');
const warBtn = document.getElementById('war');
const searchBtn = document.getElementById('searchBtn');


const newsQuery = document.getElementById('newsQuery');
const newsType = document.getElementById('newsType');
const newsdetails = document.getElementById('newsdetails');

var newsDataArr = [];


//apis
const API_KEY = "7eaa7b2e9d4945bfaf698769ba0a3a98";
const HEADLINES_NEWS = "https:://newsapi.org/v2/top-headlines?country=us&apiKey=";
const GENERAL_NEWS = "https:://newsapi.org/v2/top-headlines?country=us&category=general&apiKey=";
const CYBER_NEWS = "https:://newsapi.org/v2/top-headlines?country=us&category=cybersecurity&apiKey=";
const HACKS_NEWS = "https:://newsapi.org/v2/top-headlines?country=us&category=hacks&apiKey=";
const WAR_NEWS = "https:://newsapi.org/v2/top-headlines?country=us&category=cyberwarfare&apiKey=";
const SEARCH_NEWS = "https:://newsapi.org/v2/everything?q=";


window.onload = function() {
    newsType.innerHTML = "<h4>Headlines</h4>"; 
    fetchHeadlines();
};




generalBtn.addEventListener('click', function() {
    newsType.innerHTML = "<h4>General News</h4>"; 
    fetchGeneralNews();
});
cyberBtn.addEventListener('click', function() {
    newsType.innerHTML = "<h4>Cybersecurity News</h4>"; 
    fetchCyberNews();
});
hacksBtn.addEventListener('click', function() {
    newsType.innerHTML = "<h4>Computer Hacks</h4>"; 
    fetchHacksNews();
});
warBtn.addEventListener('click', function() {
    newsType.innerHTML = "<h4>CyberWarfare News</h4>"; 
    fetchWarNews();
});
searchBtn.addEventListener('click', function() {
    newsType.innerHTML = "<h4>Search :"+newsQuery.value+"</h4>"; 
    fetchQuryNews();
});



const fetchHeadlines = async () => {
    const response = await fetch(HEADLINES_NEWS+API_KEY);
    newsDataArr = [];
    if(response.status >=200 && response.status < 300 ) {
        const myJson = await response.json();
        console.log(myJson);
        newsDataArr = myJson.articles;
    } else {
        console.log(response.status, response.statusText);
    }



    displayNews();
}
const fetchGeneralNews = async () => {
    const response = await fetch(GENERAL_NEWS+API_KEY);
    newsDataArr = [];
    if(response.status >=200 && response.status < 300 ) {
        const myJson = await response.json();
        console.log(myJson);
        newsDataArr = myJson.articles;
    } else {
        console.log(response.status, response.statusText);
    }



    displayNews();
}

const fetchCyberNews = async () => {
    const response = await fetch(CYBER_NEWS+API_KEY);
    newsDataArr = [];
    if(response.status >=200 && response.status < 300 ) {
        const myJson = await response.json();
        newsDataArr = myJson.articles;
    }else {
        console.log(response.status, response.statusText);
    }



    displayNews();
}

const fetchHacksNews = async () => {
    const response = await fetch(HACKS_NEWS+API_KEY);
    newsDataArr = [];
    if(response.status >=200 && response.status < 300 ) {
        const myJson = await response.json();
        newsDataArr = myJson.articles;
    }else {
        console.log(response.status, response.statusText);
    }



    displayNews();
}

const fetchWarNews = async () => {
    const response = await fetch(WAR_NEWS+API_KEY);
    newsDataArr = [];
    if(response.status >=200 && response.status < 300 ) {
        const myJson = await response.json();
        newsDataArr = myJson.articles;
    }else {
        console.log(response.status, response.statusText);
    }



    displayNews();
}

const fetchQuryNews = async () => {

    if(newsQuery.value = Null)
        return;


    const response = await fetch(SEARCH_NEWS+encodeURIComponent(newsQuery.value)+"&apiKey="+API_KEY);
    newsDataArr = [];
    if(response.status >=200 && response.status < 300 ) {
        const myJson = await response.json();
        newsDataArr = myJson.articles;
    }else {
        console.log(response.status, response.statusText);
    }



    displayNews();
}





function displayNews() {


    newsdetails.innerHTML = "";

    if(newsDataArr.length == 0) {
        newsdetails.innerHTML = "<h5>No data found.</h5>"
            return;
    }


    newsDataArr.forEach(news => {

        var date = news.publishedAt.split("T");

        var col = document.createElement("div");
        col.className ="col-sm-12 col-md-4 col-lg-3 p-2";

        var card = document.createElement("div");
        card.className = "p-2";

        var image = document.createElement("img");
        image.setAttribute("height", "matchparent");
        image.setAttribute("width", "100%");
        image.src=news.urlToImage;



        var cardBody = document.createElement("div");


        var newsHeading = document.createElement("h5");
        newsHeading.className = "card-title";
        newsHeading.innerHTML= news.title;


        var dateheading = document.createElement("h6");
        dateheading.className = "text-primary";
        dateheading.innerHTML = date[0];


        var discription = document.createElement("p");
        discription.className="text-muted";
        discription.innerHTML = news.discription


        var link = document.createElement("a");
        link.className="btn btn-dark";
        link.setAttribute("traget", "_blank");
        link.href = news.url;
        link.innerHTML = "Read more";


        cardBody.appendChild(newsHeading);
        cardBody.appendChild(dateHeading);
        cardBody.appendChild(discription);
        cardBody.appendChild(link)

        card.appendChild(image);
        card.appendChild(cardBody);

        col.appendChild(card);



        newsdetails.appendChild(col);
    })
}