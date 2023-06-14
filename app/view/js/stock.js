window.onload = function () {

    /*let 
        $assets = document.querySelectorAll ('.list tbody tr'),
        $xhr     = new XMLHttpRequest (),
        $url     = '/ajax-get-data';

    [].forEach.call($assets, function(elem) {

        elem.querySelector('.assets').addEventListener ('keyup', data);
        elem.querySelector('input[name="portfolio"]').addEventListener ('click', function () {
            
            const $params = JSON.stringify ({
                secid     : this.dataset.secid,
                portfolio : this.checked
            });
            
            $xhr.open ('POST', '/ajax-add-portfolio', true);
            $xhr.setRequestHeader('Content-type', 'application/json; charset=utf-8');
            $xhr.send ($params);

            $xhr.onreadystatechange = function() {
                if (this.readyState === 4) {

                    if (this.status != 200) {

                        // обработать ошибку
                        alert('Ошибка ' + this.status + ': ' + this.statusText);

                    } else {
                        //console.log(this.responseText);
                        let $result = JSON.parse (this.responseText);

                        if ($result['status'] === 200)
                            location.reload();

                        if ($result['status'] === 400)
                            console.log('Ошибка');

                    }
                }
            }
        });

        //elem.querySelector('.assets').addEventListener ('mouseout', data);
        

        function data() {

            const $params = JSON.stringify ({
                secid  : this.dataset.secid,
                assets : this.value
            });
            
            $xhr.open ('POST', $url, true);
            $xhr.setRequestHeader('Content-type', 'application/json; charset=utf-8');
            $xhr.send ($params);

            $xhr.onreadystatechange = function() {
                if (this.readyState === 4) {

                    if (this.status != 200) {

                        // обработать ошибку
                        alert('Ошибка ' + this.status + ': ' + this.statusText);

                    } else {
                        //console.log(this.responseText);
                        let $result = JSON.parse (this.responseText);

                        if ($result['status'] === 200)
                            elem.querySelector('.actual_price').innerText = $result['actual_price'];

                        if ($result['status'] === 400) {
                            //alert('В поле "Капитализация должны быть только цыфры!"');
                            elem.querySelector('.assets').value = '';
                            //elem.querySelector('.actual_price').innerText = '0,00'

                        }

                    }
        
                    
                    
                }
            }
            //console.log();
        }

    });*/



    /**
    *  Готовим модальное окно на странице листинга акций
    * 
    */
    const elems = document.querySelectorAll ('.list tbody tr');

    [].forEach.call(elems, function(el)
    {
        el.querySelector('input[name="idea"]').addEventListener ('click', function () 
        {
            const idea = ajax('/idea', 'post',
                {
                    secid  : this.dataset.secid,
                    idea : this.checked
                }
            )

            idea.onreadystatechange = () => 
            {
                if (idea.readyState === 4 && idea.status === 200)
                    return 200;
            }
        })
        
        el.querySelector('.name').addEventListener('click', function() 
        {
             const 
                $_this    = el,
                tiker     = $_this.querySelector('.tiker').innerText,
                issuesize = $_this.querySelector('.issuesize').innerText,
                modalTpl  = ajax ('/modal-info', 'post', {secid : tiker});

            modalTpl.onreadystatechange = () =>
            {
                if (modalTpl.readyState === 4) 
                {
                    if (modalTpl.status != 200) // обработать ошибку
                        return 'Ошибка ' + modalTpl.status + ': ' + modalTpl.statusText;
                    else 
                    {
                        document.body.insertAdjacentHTML('afterbegin', modalTpl.responseText);
    
                        const modal = document.querySelector('.modal');
    
                        modal.style.cssText= 'display: block; background-color: rgba(0, 0, 0, .5)';
                        modal.classList.add('show');
    
                        document.querySelector('#close').addEventListener( 'click', () => modal.remove() );
    
                        // Получаем данные, отправляем на сервер
                        document.querySelector('#save').addEventListener('click', function() 
                        {
                            const 
                                data = {
                                    secid     : tiker,
                                    issuesize : issuesize,
                                    assets    : modal.querySelector('#assets').value,
                                    portfolio : (modal.querySelector('#portfolio')  !== null ) ? modal.querySelector('#portfolio').value : '',
                                    user_id   : modal.dataset.userid
                                }

                            const updateData = ajax ('/update-data', 'post', data);
                            
                            updateData.onreadystatechange = () => 
                            {
                                if (updateData.readyState === 4 && updateData.status === 200) 
                                {
                                    const result = JSON.parse (updateData.responseText)

                                    if (result['result'] !== '') {
                                        $_this.querySelector('.assets').innerText = result['result']['assets'],
                                        $_this.querySelector('.actual_price').innerText = result['result']['actual_price']
                                    }
                                    
                                }
                            }
                            
                        })
                    }     
                }
                
            }
        })
    })
    
}