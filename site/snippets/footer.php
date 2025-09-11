    <footer class="main-footer">
        <p>©Seongki Sohn</p>
        <ul class="footer-menu">
            <?php foreach ($site->children()->listed() as $item) { ?>
            <li><a href="<?= $item->url() ?>"><?= $item->title() ?></a></li>
            <?php } ?>    
        </ul>
    </footer>
</div>
</body>
</html>