/**
    * Ajax
    * 
    */
 function ajax (url, method = 'POST', data = {})
 {

     if (typeof url === 'undefined' || url === null)
         return false;
     
     const $xhr = new XMLHttpRequest ();
     $xhr.open (method, url, true);
     $xhr.setRequestHeader('Content-type', 'application/json; charset=utf-8');
     
     if ( Object.keys(data).length === 0 )
         $xhr.send ();
     else 
         $xhr.send ( JSON.stringify (data) ); 

     return $xhr
 }