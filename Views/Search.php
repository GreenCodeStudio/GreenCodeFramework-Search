<div class="grid">
    <section class="card" data-width="6">
        <header>
            <h1>Wyszukiwanie</h1>
        </header>
        <form>
            <input type="search" value="<?= htmlspecialchars($data['query']) ?>">
        </form>
        <div class="searchResults">
            <?php foreach ($data['results'] as $g) {
                ?>
                <h2><?= htmlspecialchars($g->name) ?></h2>
                <?php
                foreach ($g->items as $result) {
                    ?>
                    <a href="<?= htmlspecialchars($result->link) ?>">
                        <div>
                            <?= htmlspecialchars($result->name) ?>
                        </div>
                    </a>
                    <?php
                }
            } ?>
        </div>
    </section>
</div>