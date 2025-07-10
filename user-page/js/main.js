var header = document.getElementById('menu');
var btns = document.getElementsByClassName('tab');
for(var i = 0; i < btns.length; i++){
    btns[i].addEventListener('click', function() {
        var current = document.getElementsByClassName('active');
        current[0].className = current[0].className.replace(' active', '');
        this.className += ' active';
    })
}



const userCont = document.querySelector('.users');

const users = [
    {
        id: 1,
        img: '<img src="../img/8274768.jpg" alt="President Profile Pic">',
        header: "Bright",
    },
    {
        id: 2,
        img: '<img src="../img/8274768.jpg" alt="President Profile Pic">',
        header: "David",
    },
    {
        id: 3,
        img: '<img src="../l-img/incog.png" alt="President Profile Pic">',
        header: "Shobai",
    },
    {
        id: 4,
        img: '<img src="../img/8274768.jpg" alt="President Profile Pic">',
        header: "Margaret",
    },
    {
        id: 5,
        img: '<img src="../img/8274768.jpg" alt="President Profile Pic">',
        header: "Portia",
    },
    {
        id: 6,
        img: '<img src="../img/8274768.jpg" alt="President Profile Pic">',
        header: "Divine",
    },
    {
        id: 7,
        img: '<img src="../img/8274768.jpg" alt="President Profile Pic">',
        header: "Cyril",
    },
    {
        id: 8,
        img: '<img src="../l-img/1684221851_Tokyo REvengers (3).jpg" alt="President Profile Pic">',
        header: "Morgan",
    },
    {
        id: 9,
        img: '<img src="../img/8274768.jpg" alt="President Profile Pic">',
        header: "Lomotey",
    },
]

const data = users.map((user) => {
    return `
        <div class="user">
            <div class="user-img">
                ${user.img}
            </div>
            <div class="name">
                <h3> ${user.header} </h3>
            </div>
        </div>
    `
}).join("")

userCont.innerHTML = data;

