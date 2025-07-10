var header = document.getElementById('menu');
var btns = document.getElementsByClassName('tab');
for(var i = 0; i < btns.length; i++){
    btns[i].addEventListener('click', function() {
        var current = document.getElementsByClassName('active');
        current[0].className = current[0].className.replace(' active', '');
        this.className += ' active';
    })
}



var header = document.getElementById('mobile');
var tabs = document.getElementsByClassName('btn');
for(var i = 0; i < tabs.length; i++){
    tabs[i].addEventListener('click', function() {
        var curent = document.getElementsByClassName('online');
        curent[0].className = curent[0].className.replace(' online', '');
        this.className += ' online';
    })
}


var menu_btn = document.querySelector('.humburger');
var mobile_btn = document.querySelector('.mobile')
menu_btn.addEventListener('click', function() {
    menu_btn.classList.toggle('is-active');
    mobile_btn.classList.toggle('is-active')
})