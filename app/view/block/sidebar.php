<aside id="sidebar">
    <nav class="nav flex-column">
        <a class="nav-link active" aria-current="page" href="/">Главная</a>
        <a class="nav-link" aria-current="page" href="/shares">Список акций</a>
        <a class="nav-link" aria-current="page" href="/portfolio">Портфели</a>
    </nav>

    
    <?php if ( $this->request->session()['role'] == 'admin' ) : ?>
        <nav class="nav flex-column">
            <a class="nav-link btn btn-success mt-5" aria-current="page" href="/update-data-exchange">Обновить данные</a>
        </nav>
    <?php endif ?>
    
</aside>