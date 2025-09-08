// assets/script.js
document.addEventListener('DOMContentLoaded', function(){
  // Example: confirm on some destructive action (not used by default)
  document.querySelectorAll('.confirm-delete').forEach(btn => {
    btn.addEventListener('click', function(e){
      if(!confirm('Are you sure?')) e.preventDefault();
    });
  });
});
