    <footer class="main-footer">
        <p>©Seongki Sohn</p>
        <ul class="footer-menu">
            <?php foreach ($site->children()->listed() as $item) { ?>
            <li><a href="<?= $item->url() ?>"><?= $item->title() ?></a></li>
            <?php } ?>
            <li><a href="https://www.sohnseongki.com">Portfolio</a></li>
        </ul>
    </footer>
</div>
<?php snippet("seo/schemas"); ?>
</body>
</html>
