<div class="prod">
    <?php if (!empty($product['sale'])): ?>
        <a class="sale">-<?php echo htmlspecialchars($product['sale']); ?>%</a>
    <?php endif; ?>
    <a href="/product.php?id=<?php echo htmlspecialchars($product['product_id']); ?>">
        <img class="prod_pic" src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
        <div class="desc">
            <span class="prod_name"><?php echo htmlspecialchars($product['product_name']); ?></span>
        </div>
        <div class="card_price">
            <span class="price"><?php echo number_format($product['price'], 0, '', ' '); ?></span>
            <span class="price_sign">₽</span>
        </div>
        <div class="average-rating">
            <?php if ($product['average_rating'] > 0): ?>
                <div class="rank-container">
                    <p class="rank-text">RANK:</p>
                    <span class="rank rank-<?php echo htmlspecialchars($product['average_rank']); ?>">
                        <?php echo htmlspecialchars($product['average_rank']); ?>
                    </span>
                </div>
                <span class="rating-value">(<?php echo number_format($product['average_rating'], 1); ?>/5)</span>
            <?php else: ?>
                <span class="no_reviews">Нет оценок</span>
            <?php endif; ?>
        </div>
    </a>
    <div class="btns">
        <?php if ($product['available'] > 0): ?>
            <form action="/vendor/cart" method="post">
                <input type="hidden" name="productID" value="<?php echo htmlspecialchars($product['product_id']); ?>">
                <input type="hidden" name="action" value="active">
                <button type="submit" class="cart_but">В корзину</button>
            </form>
        <?php else: ?>
            <button type="button" class="cart_but prod_unavailable" disabled>Нет в наличии</button>
        <?php endif; ?>
        <form action="/vendor/wishlist" method="post">
            <input type="hidden" name="productID" value="<?php echo htmlspecialchars($product['product_id']); ?>">
            <button type="submit" name="action" value="active" class="fav_but <?php echo checkWishlist($product['product_id']) ? 'wishlist' : ''; ?>">
                <svg width="30px" height="30px" viewBox="0 0 32 32">
                    <path d="M26 1.25h-20c-0.414 0-0.75 0.336-0.75 0.75v0 28.178c0 0 0 0 0 0.001 0 0.414 0.336 0.749 0.749 0.749 0.181
                    0 0.347-0.064 0.476-0.171l-0.001 0.001 9.53-7.793 9.526 7.621c0.127 0.102 0.29 0.164 0.468 0.164 0.414 0 0.75-0.336
                    0.751-0.75v-28c-0-0.414-0.336-0.75-0.75-0.75v0z"/>
                </svg>
            </button>
        </form>
    </div>
</div>