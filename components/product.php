<div class="prod">
    <a href="/product.php?id=<?php echo $product['product_id'] ?>">
        <div class="upper">
            <?php if (isset($product['sale'])): ?>
                <span class="sale">-<?php echo $product['sale']; ?>%</span>
            <?php endif; ?>
            <img class="prod_pic" src="<?php echo $product['image']; ?>">
            <div class="desc">
                <span class="prod_name"><?php echo $product['product_name']; ?></span>
            </div>
        </div>
        <div class="lower">
            <div class="card_price">
                <span class="price"><?php echo number_format($product['price'], 0, '', ' '); ?></span>
                <span class="price_sign">₽</span>
            </div>
            <!-- Средний рейтинг в виде ранга -->
            <div class="average-rating">
                <?php
                global $productRatings;
                $averageRating = $productRatings[$product['product_id']] ?? 0;
                if ($averageRating > 0) {
                    $averageRank = getRankFromRating($averageRating);
                    echo '<div class="rank-container">';
                    echo '<p class="rank-text">RANK:</p> <span class="rank rank-' . $averageRank . '">' . $averageRank . '</span>';
                    echo '</div>';
                    echo '<span class="rating-value">(' . number_format($averageRating, 1) . '/5)</span>';
                } else {
                    echo "Нет оценок";
                }
                ?>
            </div>
            <div class="btns">
                <?php if ($product['available'] > 0): ?>
                    <form action="/vendor/cart" method="post">
                        <input type="hidden" name="productID" value="<?php echo $product['product_id'] ?>">
                        <input type="hidden" name="action" value="active">
                        <button type="submit" class="cart_but">В корзину</button>
                    </form>
                <?php else: ?>
                    <button class="cart_unavailable">Нет в наличии</button>
                <?php endif; ?>
                <form action="/vendor/wishlist" method="post">
                    <input type="hidden" name="productID" value="<?php echo $product['product_id'] ?>">
                    <button type="submit" name="action" value="active" class="fav_but <?php echo (checkWishlist($product['product_id']) ? 'wishlist' : ''); ?>">
                        <svg class="fav_img" width="33px" height="33px" viewBox="0 0 32 32">
                            <path d="M26 1.25h-20c-0.414 0-0.75 0.336-0.75 0.75v0 28.178c0 0 0 0 0 0.001 0 0.414 0.336 0.749 0.749 0.749 0.181
                            0 0.347-0.064 0.476-0.171l-0.001 0.001 9.53-7.793 9.526 7.621c0.127 0.102 0.29 0.164 0.468 0.164 0.414 0 0.75-0.336
                            0.751-0.75v-28c-0-0.414-0.336-0.75-0.75-0.75v0z"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </a>
</div>