document.addEventListener('DOMContentLoaded', function () 
{
    
    /**
     * Выводим график
     *  
     */
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {

        const options = {
            title: 'Мои активы',
            pieHole: 1,
            pieSliceTextStyle: {
                color: 'black',
            },
            //legend: {position : 'top'},
            width: document.querySelector('.accordion-header').clientWidth - 50,
            height: 300,
            chartArea: {
                left: 20,
                top: 20,
                width: '100%',
                height: '100%'
            }
        };
        
        [].forEach.call(document.querySelectorAll('.portfolio'), (portfolio) =>
        {
            const arrData = [['Task', 'Hours per Day']];

            [].forEach.call(portfolio.querySelectorAll('tbody tr'), (elem) =>  arrData.push(
                    [ elem.querySelector('.tiker').innerText, Number( elem.querySelector('.total_buy_price').innerText) ]
                )
            )
            
            if (arrData.length > 1 ) 
            {
                const 
                    data = google.visualization.arrayToDataTable( arrData ),
                    chart = new google.visualization.PieChart( portfolio.querySelector('.donut_single') );
                
                    chart.draw(data, options);
            }
                
        })

    }



    /**
     * 
     * 
     */
    [].forEach.call(document.querySelectorAll('.portfolio'), (portfolio) => 
    {
        const arr = {
            price : [],
            yield : []
        };

        [].forEach.call(portfolio.querySelectorAll('tbody tr'), (elem) => 
        {
            // Удаляем актив из портфеля
            elem.querySelector('.trash').addEventListener('click', function () 
            {
                const trash = ajax ('/portfolio-remove-assets', 'post', {
                    tiker        : this.dataset.tiker,
                    portfolio_id : this.dataset.portfolio
                })
                
                trash.onreadystatechange = () => 
                {
                    if ( trash.readyState === 4 && trash.status === 200 ) 
                    {
                        const result = JSON.parse ( trash.responseText );
                        
                        if ( result['code'] == 200 ) 
                            this.parentNode.remove()
                        else return;
                        
                    }
                }
            })

            /*elem.querySelector('.edit').addEventListener('click', function () 
            {
                const edit = ajax ('/portfolio-edit-assets', 'post', {
                    tiker        : this.dataset.tiker,
                    portfolio_id : this.dataset.portfolio
                })
                
                edit.onreadystatechange = () => 
                {
                    if ( edit.readyState === 4 && edit.status === 200 ) 
                    {
                        const result = JSON.parse ( edit.responseText );
                        
                        if ( result['code'] == 200 ) 
                            //this.parentNode.remove()
                        else return;
                        
                    }
                }
            })*/

            // Порлучаем итоговые суммы инвестиций и процентов
            arr.price.push( Number( elem.querySelector('.total_buy_price').innerText ) )
            arr.yield.push( Number( elem.querySelector('.price').innerText ) * Number( elem.querySelector('.quantity').innerText ) )
        })

        // Выводим в итогах портфеля сумму инвестиций и доходность в процентах
        if (arr.price.length > 0 && arr.yield.length > 0) 
        {
            const 
                totalPrice = arr.price.reduce( (sum, current) => sum + current),
                totalYield = ( ( ( arr.yield.reduce( (sum, current) => sum + current) /  arr.price.reduce( (sum, current) => sum + current) ) - 1 ) * 100 ).toFixed(2) + ' %' 

            portfolio.querySelector('.total_price').innerText = totalPrice
            portfolio.querySelector('.total_yield').innerText = totalYield

            const 
                div = document.createElement('div')

                div.style.cssText = `
                    padding-right: 60px;
                    position : absolute;
                    top: 0;
                    right: 0;
                    height: 100%;
                    display: flex;
                    align-items: center;
                    font-size: 1em;
                `
                div.innerHTML = `<span>Инвестиции: ${totalPrice} / Доходность: ${totalYield} </span>`
            
            portfolio.querySelector('.accordion-button').style.cssText = 'position: relative;'
            portfolio.querySelector('.accordion-button').append(div)
        }

        // Редактируем имя портфеля
        /*portfolio.querySelector('.edit').addEventListener('click', () =>
        {
            portfolio.querySelector('input').disabled = ''
            portfolio.querySelector('.accordion-collapse').classList.toggle('show')
            console.log(portfolio.querySelector('input'))
        })*/
    })


})