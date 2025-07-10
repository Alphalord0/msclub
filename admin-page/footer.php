</body>


<script>


$(document).ready(function(){
    var ids = new Array();
    $('#over').on('click',function(){
           $('#list').toggle();  
       });

   //Message with Ellipsis
   $('div.msg').each(function(){
       var len =$(this).text().trim(" ").split(" ");
      if(len.length > 12){
         var add_elip =  $(this).text().trim().substring(0, 65) + "…";
         $(this).text(add_elip);
      }
     
}); 


   $("#bell-count").on('click',function(e){
        e.preventDefault();

        let belvalue = $('#bell-count').attr('data-value');
        
        if(belvalue == ''){
         
          console.log("inactive");
        }else{
          $(".round").css('display','none');
          $("#list").css('display','block');
          
          // $('.message').each(function(){
          // var i = $(this).attr("data-id");
          // ids.push(i);
          
          // });
          //Ajax
          $('.message').click(function(e){
            e.preventDefault();
              $.ajax({
                url:'./connection/deactive.php',
                type:'POST',
                data:{"id":$(this).attr('data-id')},
                success:function(data){
                 
                    console.log(data);
                    location.reload();
                }
            });
        });
     }
   });

   $('#notify').on('click',function(e){
        e.preventDefault();
        var name = $('#notifications_name').val();
        var ins_msg = $('#message').val();
        if($.trim(name).length > 0 && $.trim(ins_msg).length > 0){
          var form_data = $('#frm_data').serialize();
        $.ajax({
          url:'./connection/insert.php',
                type:'POST',
                data:form_data,
                success:function(data){
                    location.reload();
                }
        });
        }else{
          alert("Please Fill All the fields");
        }
      
       
   });
});


function myBtn() {
  document.getElementById("menu").classList.toggle("is-close");
  document.getElementById("interface").classList.toggle("is-close");
  document.getElementById("menu").classList.toggle("is-clos");

}

window.ondblclick = function(event) {
  if (!event.target.matches('.barrs')) {
    var dropdowns = document.getElementsByClassName("menu");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('is-clos')) {
        openDropdown.classList.remove('is-clos');
      }
    }
  }
}


        document.addEventListener('DOMContentLoaded', function() {
            const searchQueryInput = document.getElementById('searchQuery');
            const searchResultsDiv = document.getElementById('searchResults');
            const userTableBody = searchResultsDiv.querySelector('tbody'); // Get the tbody of the already existing table
            let allTableRows = []; // To store all table rows for filtering

            // Function to initialize by gathering all table rows
            function initializeTableRows() {
                if (userTableBody) {
                    allTableRows = Array.from(userTableBody.children); // Convert HTMLCollection to Array
                    if (allTableRows.length === 0) {
                        searchResultsDiv.innerHTML = '<p class="message">No users found in the table.</p>';
                    }
                } else {
                    searchResultsDiv.innerHTML = '<p class="text-red-500">Error: User table not found. Make sure your PHP code outputs the table inside #searchResults.</p>';
                }
            }

            // Function to filter and display users from the existing table
            function filterAndDisplayUsers() {
                const query = searchQueryInput.value.trim().toLowerCase(); // Get query and convert to lowercase for case-insensitive search
                let foundResults = 0;

                allTableRows.forEach(row => {
                    // Get text content of username (2nd td, index 1) and full name (3rd td, index 2)
                    const username = row.children[1] ? row.children[1].textContent.toLowerCase() : '';
                    const fullName = row.children[2] ? row.children[2].textContent.toLowerCase() : '';

                    if (query === '' || username.includes(query) || fullName.includes(query)) {
                        row.style.display = ''; // Show the row
                        foundResults++;
                    } else {
                        row.style.display = 'none'; // Hide the row
                    }
                });

                // Display a message if no results are found after filtering
                const noResultsMessageId = 'no-results-message';
                let noResultsMessage = document.getElementById(noResultsMessageId);

                if (foundResults === 0 && query !== '') {
                    if (!noResultsMessage) {
                        noResultsMessage = document.createElement('p');
                        noResultsMessage.id = noResultsMessageId;
                        noResultsMessage.className = 'message';
                        searchResultsDiv.insertBefore(noResultsMessage, userTableBody); // Insert before the table body
                    }
                    noResultsMessage.textContent = `No users found matching "${query}".`;
                    userTableBody.style.display = 'none'; // Hide the table body if no results
                } else {
                    if (noResultsMessage) {
                        noResultsMessage.remove(); // Remove message if results are found or query is empty
                    }
                    userTableBody.style.display = ''; // Show the table body
                }
            }

            // Initialize the rows when the page loads
            initializeTableRows();

            // Event listener for input changes (typing)
            searchQueryInput.addEventListener('keyup', filterAndDisplayUsers);
            searchQueryInput.addEventListener('paste', filterAndDisplayUsers); // Also trigger on paste

            // Trigger initial filter to ensure correct display if search box has content on load
            filterAndDisplayUsers();
        });

</script>


</html>