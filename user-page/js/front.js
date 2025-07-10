function myFunction() {
    var x = document.getElementById("myInput");
    if (x.type === "password") {
      x.type = "text";
    } else {
      x.type = "password";
    }
}


window.onload = function() {
    const eye = document.querySelector('.moon');
    eye.addEventListener('click', function() {
        eye.classList.toggle('is-active')
    })

}




const form = document.getElementById('form');

form.addEventListener('submit', function(e) {
    var username = document.getElementById('user').value;
    var pass = document.getElementById('myInput').value;

  console.log(pass)
  if (username == 'cyber' && pass == '2025'){}
  else{
    e.preventDefault();
    alert('Wrong Username or Password');
  }
})