(function(){
  function ready(fn){
    if(document.readyState !== 'loading'){ fn(); } else { document.addEventListener('DOMContentLoaded', fn); }
  }
  function toggleSubOptions(row){
    var checkbox = row.querySelector('input[type="checkbox"]');
    var subs = row.querySelectorAll('.xwp-ats-suboptions');
    if(!checkbox || !subs.length) return;
    function sync(){
      for (var i=0;i<subs.length;i++) {
        subs[i].style.display = checkbox.checked ? '' : 'none';
      }
    }
    checkbox.addEventListener('change', sync);
    sync();
  }
  ready(function(){
    var rows = document.querySelectorAll('.xwp-ats-row');
    for (var i=0;i<rows.length;i++) {
      toggleSubOptions(rows[i]);
    }
  });
})();
