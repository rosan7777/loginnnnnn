<?php
session_start();
include("connection/connection.php");

// Define user_id consistently
$user_id = isset($_SESSION["USER_ID"]) ? (int)$_SESSION["USER_ID"] : 0;
$Search_text = isset($_GET["value"]) ? trim($_GET["value"]) : '';

// Fetch trader information for navbar
$trader_shop = [];
$sql = "SELECT u.USER_ID, s.SHOP_NAME AS NAME, u.USER_PROFILE_PICTURE, s.SHOP_DESCRIPTION
        FROM CLECK_USER u JOIN SHOP s ON u.USER_ID = s.USER_ID 
        WHERE u.USER_TYPE = 'trader'";
$stmt = oci_parse($conn, $sql);
if (!oci_execute($stmt)) {
    $e = oci_error($stmt);
    error_log("Trader query failed: " . $e['message']);
}
while ($row = oci_fetch_assoc($stmt)) {
    $description = $row['SHOP_DESCRIPTION'];
    $words = explode(' ', trim($description));
    $row['SHOP_DESCRIPTION'] = implode(' ', array_slice($words, 0, 10));
    $trader_shop[] = $row;
}
oci_free_statement($stmt);

// Fetch categories
$categoryArray = [];
$sql = "SELECT CATEGORY_ID, CATEGORY_TYPE FROM PRODUCT_CATEGORY";
$result = oci_parse($conn, $sql);
oci_execute($result);
while ($row = oci_fetch_assoc($result)) {
    $categoryArray[] = $row;
}
oci_free_statement($result);

// Fetch selected category name
$category_name = '';
$category = isset($_POST["category"]) ? sanitizeInteger($_POST["category"]) : (isset($_GET["category_id"]) ? sanitizeInteger($_GET["category_id"]) : 0);
if ($category != 0) {
    $sql = "SELECT CATEGORY_TYPE FROM PRODUCT_CATEGORY WHERE CATEGORY_ID = :category_id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':category_id', $category);
    oci_execute($stmt);
    if ($row = oci_fetch_assoc($stmt)) {
        $category_name = $row['CATEGORY_TYPE'];
    }
    oci_free_statement($stmt);
} else {
    $category_name = 'All Products';
}

// Sanitization functions
function sanitizeInteger($input) {
    return (int)preg_replace("/[^0-9]/", "", $input);
}

function sanitizeString($input) {
    return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
}

// Filter and sort variables
$min_price = isset($_POST["min-price"]) && $_POST["min-price"] !== '' ? sanitizeInteger($_POST["min-price"]) : 0;
$max_price = isset($_POST["max-price"]) && $_POST["max-price"] !== '' ? sanitizeInteger($_POST["max-price"]) : PHP_INT_MAX;
$sort_by = isset($_POST["sort-by"]) ? sanitizeString($_POST["sort-by"]) : null;
$rating = isset($_POST["rating"]) && $_POST["rating"] !== '' ? sanitizeInteger($_POST["rating"]) : null;

// Prepare SQL query
$sql = "SELECT 
    p.PRODUCT_ID, 
    p.PRODUCT_NAME, 
    p.PRODUCT_PRICE, 
    p.PRODUCT_PICTURE, 
    p.PRODUCT_QUANTITY,
    COALESCE(s.SHOP_NAME, 'Unknown Shop') AS SHOP_NAME,
    r.AVG_REVIEW_SCORE,
    r.TOTAL_REVIEWS,
    COALESCE(d.DISCOUNT_PERCENT, '') AS DISCOUNT_PERCENT
FROM 
    product p
LEFT JOIN (
    SELECT PRODUCT_ID, AVG(REVIEW_SCORE) AS AVG_REVIEW_SCORE, COUNT(REVIEW_SCORE) AS TOTAL_REVIEWS
    FROM review
    GROUP BY PRODUCT_ID
) r ON p.PRODUCT_ID = r.PRODUCT_ID
LEFT JOIN 
    discount d ON p.PRODUCT_ID = d.PRODUCT_ID
LEFT JOIN 
    shop s ON p.user_id = s.user_id
WHERE 
    p.IS_DISABLED = 1 
    AND p.ADMIN_VERIFIED = 1
    AND UPPER(p.PRODUCT_NAME) LIKE '%' || UPPER(:search_text) || '%'";

if ($min_price !== null && $max_price !== null) {
    $sql .= " AND p.PRODUCT_PRICE BETWEEN :min_price AND :max_price";
}
if ($category != 0) {
    $sql .= " AND p.CATEGORY_ID = :category";
}
if ($rating !== null) {
    $sql .= " AND (r.AVG_REVIEW_SCORE >= :rating OR r.AVG_REVIEW_SCORE IS NULL)";
}

switch ($sort_by) {
    case "alphabetically_asc": $sql .= " ORDER BY p.PRODUCT_NAME ASC"; break;
    case "alphabetically_desc": $sql .= " ORDER BY p.PRODUCT_NAME DESC"; break;
    case "price-low-to-high": $sql .= " ORDER BY p.PRODUCT_PRICE ASC"; break;
    case "price-high-to-low": $sql .= " ORDER BY p.PRODUCT_PRICE DESC"; break;
    default: $sql .= " ORDER BY p.PRODUCT_ID DESC"; break;
}

// Execute query
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':search_text', $Search_text);
if ($min_price !== null && $max_price !== null) {
    oci_bind_by_name($stmt, ':min_price', $min_price);
    oci_bind_by_name($stmt, ':max_price', $max_price);
}
if ($category != 0) {
    oci_bind_by_name($stmt, ':category', $category);
}
if ($rating !== null) {
    oci_bind_by_name($stmt, ':rating', $rating);
}
if (!oci_execute($stmt)) {
    $e = oci_error($stmt);
    die("Query execution failed: " . $e['message']);
}

// Fetch results
$numRows = 0;
$fetchedRows = [];
while ($row = oci_fetch_assoc($stmt)) {
    $numRows++;
    $fetchedRows[] = $row;
}
oci_free_statement($stmt);
oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Page - Cleckfax Traders</title>
    <link rel="icon" href="logo_ico.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f0f8ff; }
        .navbar-item.nav-link { position: relative; }
        .navbar-item.nav-link::after {
            content: ''; position: absolute; width: 0; height: 2px; bottom: 0; left: 0;
            background-color: #48c774; transition: width 0.3s ease;
        }
        .navbar-item.nav-link:hover::after { width: 100%; }
        .navbar-item.nav-link:hover { color: #48c774 !important; }
        .container_search { display: flex; margin: 2rem auto; max-width: 1200px; }
        .left-sidebar { width: 25%; padding: 1rem; background-color: #fff; border-radius: 6px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); }
        .right-content { width: 75%; padding: 1rem; }
        .top-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .product-card-container { display: flex; flex-wrap: wrap; gap: 1rem; }
        .product-card { width: calc(33.33% - 1rem); background-color: #fff; border-radius: 6px; 
                       box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); cursor: pointer; 
                       transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .product-card:hover { transform: scale(1.03); box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2); }
        .product-image img { width: 100%; height: 200px; object-fit: cover; 
                           border-top-left-radius: 6px; border-top-right-radius: 6px; }
        .product-details { padding: 1rem; }
        .product-name { font-size: 1rem; font-weight: bold; margin-bottom: 0.5rem; }
        .shop-name { font-size: 0.9rem; color: #4a4a4a; margin-bottom: 0.5rem; }
        .product-rating { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; }
        #original_price { font-size: 1rem; }
        #discount { color: #ff3860; font-weight: bold; }
        #discount_price { font-size: 1.2rem; font-weight: bold; color: #3273dc; }
        .button-container { display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; padding-top: 0.5rem; }
        .buy-now-btn { background-color: #28a745; color: #fff; border: none; padding: 0.5rem 1rem; 
                      border-radius: 4px; cursor: pointer; transition: background-color 0.3s ease; }
        .buy-now-btn:hover { background-color: #218838; }
        .buy-now-btn:disabled { background-color: #dbdbdb; cursor: not-allowed; }
        .add-to-cart-btn { background-color: #3273dc; color: #fff; border: none; padding: 0.5rem 1rem; 
                          border-radius: 4px; cursor: pointer; transition: background-color 0.3s ease; 
                          display: flex; align-items: center; gap: 0.5rem; }
        .add-to-cart-btn:hover { background-color: #2557a7; }
        .add-to-cart-btn:disabled { background-color: #dbdbdb; cursor: not-allowed; }
        .wishlist-icon { background: none; border: none; color: #4a4a4a; font-size: 1rem; 
                        cursor: pointer; transition: transform 0.2s ease, color 0.2s ease; }
        .wishlist-icon:hover { transform: scale(1.2); color: #ff3860; }
        .wishlist-icon.active i { color: #ff3860; }
        @media (max-width: 768px) {
            .container_search { flex-direction: column; }
            .left-sidebar, .right-content { width: 100%; }
            .product-card { width: calc(50% - 1rem); }
        }
        @media (max-width: 480px) { .product-card { width: 100%; } }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container_search">
    <div class="left-sidebar">
        <h2 class="title is-4">Filter</h2>
        <form class="filter-form" id="price-filter" name="price-filter" action="" method="POST">
            <h3 class="subtitle is-5">Price</h3>
            <div class="field">
                <label class="label" for="min-price">Min:</label>
                <div class="control">
                    <div class="select">
                        <select name="min-price" id="min-price">
                            <option value="0" <?php echo ($min_price === 0) ? 'selected' : ''; ?>>€0</option>
                            <option value="10" <?php echo ($min_price === 10) ? 'selected' : ''; ?>>€10</option>
                            <option value="20" <?php echo ($min_price === 20) ? 'selected' : ''; ?>>€20</option>
                            <option value="30" <?php echo ($min_price === 30) ? 'selected' : ''; ?>>€30</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="field">
                <label class="label" for="max-price">Max:</label>
                <div class="control">
                    <div class="select">
                        <select name="max-price" id="max-price">
                            <option value="50" <?php echo ($max_price === 50) ? 'selected' : ''; ?>>€50</option>
                            <option value="100" <?php echo ($max_price === 100) ? 'selected' : ''; ?>>€100</option>
                            <option value="200" <?php echo ($max_price === 200) ? 'selected' : ''; ?>>€200</option>
                            <option value="500" <?php echo ($max_price === 500) ? 'selected' : ''; ?>>€500</option>
                        </select>
                    </div>
                </div>
            </div>
            <h3 class="subtitle is-5">Category</h3>
            <div class="field">
                <label class="radio">
                    <input type="radio" id="category0" name="category" value="0" <?php echo ($category == 0) ? 'checked' : ''; ?>>
                    All Products
                </label>
            </div>
            <?php foreach ($categoryArray as $cat): ?>
                <div class="field">
                    <label class="radio">
                        <input type="radio" id="category<?php echo $cat['CATEGORY_ID']; ?>" 
                               name="category" value="<?php echo $cat['CATEGORY_ID']; ?>" 
                               <?php echo ($category == $cat['CATEGORY_ID']) ? 'checked' : ''; ?>>
                        <?php echo htmlspecialchars($cat['CATEGORY_TYPE']); ?>
                    </label>
                </div>
            <?php endforeach; ?>
            <h3 class="subtitle is-5">Rating</h3>
            <div class="field">
                <div class="control">
                    <div class="select">
                        <select name="rating" id="rating">
                            <option value="" <?php echo ($rating === null) ? 'selected' : ''; ?>>Any Rating</option>
                            <option value="5" <?php echo ($rating === 5) ? 'selected' : ''; ?>>5 Stars</option>
                            <option value="4" <?php echo ($rating === 4) ? 'selected' : ''; ?>>4 Stars</option>
                            <option value="3" <?php echo ($rating === 3) ? 'selected' : ''; ?>>3 Stars</option>
                            <option value="2" <?php echo ($rating === 2) ? 'selected' : ''; ?>>2 Stars</option>
                            <option value="1" <?php echo ($rating === 1) ? 'selected' : ''; ?>>1 Star</option>
                        </select>
                    </div>
                </div>
            </div>
            <input type="hidden" name="sort-by" value="<?php echo htmlspecialchars($sort_by ?? ''); ?>">
            <input type="hidden" name="value" value="<?php echo htmlspecialchars($Search_text); ?>">
        </form>
    </div>
    <div class="right-content">
        <div class="top-section">
            <p class="title is-5">Showing <?php echo $numRows; ?> Products<?php echo $category_name ? ' in ' . htmlspecialchars($category_name) : ''; ?></p>
            <form class="sort-form" name="sort_form" id="sort_form" method="POST" action="">
                <div class="field has-addons">
                    <label class="label" for="sort-by">Sort By:</label>
                    <div class="control">
                        <div class="select">
                            <select name="sort-by" id="sort-by">
                                <option value="alphabetically_asc" <?php echo ($sort_by === 'alphabetically_asc') ? 'selected' : ''; ?>>Name: A to Z</option>
                                <option value="alphabetically_desc" <?php echo ($sort_by === 'alphabetically_desc') ? 'selected' : ''; ?>>Name: Z to A</option>
                                <option value="price-low-to-high" <?php echo ($sort_by === 'price-low-to-high') ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price-high-to-low" <?php echo ($sort_by === 'price-high-to-low') ? 'selected' : ''; ?>>Price: High to Low</option>
                            </select>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="min-price" value="<?php echo htmlspecialchars($min_price ?? ''); ?>">
                <input type="hidden" name="max-price" value="<?php echo htmlspecialchars($max_price ?? ''); ?>">
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                <input type="hidden" name="rating" value="<?php echo htmlspecialchars($rating ?? ''); ?>">
                <input type="hidden" name="value" value="<?php echo htmlspecialchars($Search_text); ?>">
            </form>
        </div>
        <div class="product-card-container">
            <?php foreach ($fetchedRows as $row): ?>
                <div class="product-card" onclick="redirectToProductPage(<?php echo $row['PRODUCT_ID']; ?>)">
                    <div class="product-image">
                        <img src="product_image/<?php echo htmlspecialchars($row['PRODUCT_PICTURE']); ?>" 
                             alt="<?php echo htmlspecialchars($row['PRODUCT_NAME']); ?>">
                    </div>
                    <div class="product-details">
                        <p class="product-name"><?php echo htmlspecialchars($row['PRODUCT_NAME']); ?></p>
                        <p class="shop-name"><?php echo htmlspecialchars($row['SHOP_NAME']); ?></p>
                        <div class="product-rating">
                            <span class="stars">
                                <?php
                                $rating = round($row['AVG_REVIEW_SCORE'] ?: 0);
                                for ($i = 0; $i < 5; $i++) {
                                    echo $i < $rating ? '★' : '☆';
                                }
                                ?>
                            </span>
                            <span class="total-reviews">(<?php echo $row['TOTAL_REVIEWS'] ?: 0; ?>)</span>
                        </div>
                        <div id="price_container">
                            <div id="original_price">€<?php echo number_format($row['PRODUCT_PRICE'], 2); ?></div>
                            <?php
                            $original_price = $row['PRODUCT_PRICE'];
                            $discount_percent = $row['DISCOUNT_PERCENT'];
                            $discount_amount = ($original_price * $discount_percent) / 100;
                            $discount_price = $row['PRODUCT_PRICE'] - $discount_amount;
                            ?>
                            <div id="discount"><?php echo $discount_percent ? "-$discount_percent%" : ''; ?></div>
                            <div id="discount_price">€<?php echo number_format($discount_price, 2); ?></div>
                        </div>
                        <div class="button-container">
                            <?php if ($row['PRODUCT_QUANTITY'] <= 0): ?>
                                <button class="buy-now-btn" disabled>Buy Now</button>
                                <button class="add-to-cart-btn" disabled><i class="fas fa-shopping-cart"></i></button>
                                <button class="wishlist-icon" disabled><i class="fas fa-heart"></i></button>
                            <?php else: ?>
                                <button class="buy-now-btn" 
                                        onclick="buyNow(<?php echo $row['PRODUCT_ID']; ?>, <?php echo $user_id; ?>, '<?php echo addslashes($Search_text); ?>')">Buy Now</button>
                                <button class="add-to-cart-btn" 
                                        onclick="addToCart(<?php echo $row['PRODUCT_ID']; ?>, <?php echo $user_id; ?>, '<?php echo addslashes($Search_text); ?>')">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                                <button class="wishlist-icon" 
                                        data-product="<?php echo $row['PRODUCT_ID']; ?>" 
                                        data-user="<?php echo $user_id; ?>" 
                                        data-search="<?php echo addslashes($Search_text); ?>">
                                    <i class="fas fa-heart"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const $navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);
    $navbarBurgers.forEach(el => {
        el.addEventListener('click', () => {
            const target = el.dataset.target;
            const $target = document.getElementById(target);
            el.classList.toggle('is-active');
            $target.classList.toggle('is-active');
        });
    });

    const serverCategory = '<?php echo $category; ?>';
    const elements = document.getElementById('price-filter').elements;
    for (let i = 0; i < elements.length; i++) {
        const element = elements[i];
        if (element.name === 'category' && element.value === serverCategory) {
            element.checked = true;
        }
    }
});

const priceFilterForm = document.getElementById('price-filter');
const sortForm = document.getElementById('sort_form');

priceFilterForm.addEventListener('change', (e) => {
    const formData = {};
    const elements = priceFilterForm.elements;
    for (let i = 0; i < elements.length; i++) {
        const element = elements[i];
        if (element.name && element.type !== 'submit') {
            if (element.type === 'radio' && element.checked) {
                formData[element.name] = element.value;
            } else if (element.type !== 'radio') {
                formData[element.name] = element.value;
            }
        }
    }
    localStorage.setItem('formData', JSON.stringify(formData));
    priceFilterForm.submit();
});

document.getElementById('sort-by').addEventListener('change', function() {
    const formData = JSON.parse(localStorage.getItem('formData')) || {};
    formData['sort-by'] = this.value;
    localStorage.setItem('formData', JSON.stringify(formData));
    sortForm.submit();
});

function addToCart(productId, userId, searchText) {
    event.stopPropagation();
    if (!userId || userId === '0') {
        alert('Please log in to add items to your cart.');
        window.location.href = 'customer_signin.php?return_url=' + encodeURIComponent(window.location.href);
        return;
    }
    fetch(`add_to_cart.php?productid=${productId}&userid=${userId}&searchtext=${encodeURIComponent(searchText)}`)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                alert('Product added to cart!');
                if (confirm('Do you want to view your cart?')) {
                    window.location.href = 'cart.php';
                }
            } else {
                alert('Failed to add product to cart: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Cart Error:', error);
            alert('An error occurred while adding to cart: ' + error.message);
        });
}

function addToWishlist(productId, userId, searchText) {
    event.stopPropagation();
    if (!userId || userId === '0') {
        alert('Please log in to add items to your wishlist.');
        window.location.href = 'customer_signin.php?return_url=' + encodeURIComponent(window.location.href);
        return;
    }
    fetch(`add_to_wishlist.php?product_id=${productId}&user_id=${userId}&searchtext=${encodeURIComponent(searchText)}`)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                alert('Product added to wishlist!');
            } else {
                alert('Failed to add product to wishlist: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Wishlist Error:', error);
            alert('An error occurred while adding to wishlist: ' + error.message);
        });
}

function buyNow(productId, userId, searchText) {
    event.stopPropagation();
    window.location.href = 'checkout.php?productid=' + productId + '&userid=' + userId + 
                         '&searchtext=' + encodeURIComponent(searchText);
}

function redirectToProductPage(productId) {
    window.location.href = "product_detail.php?productId=" + productId;
}

document.querySelectorAll('.wishlist-icon').forEach(icon => {
    icon.addEventListener('click', function(e) {
        e.stopPropagation();
        const productId = this.getAttribute('data-product');
        const userId = this.getAttribute('data-user');
        const searchText = this.getAttribute('data-search');
        addToWishlist(productId, userId, searchText);
        this.classList.toggle('active');
    });
});
</script>
</body>
</html>