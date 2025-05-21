<?php
// File: C:\xampp\htdocs\FinalProject\cleckfax\input_validation\input_sanitization.php

/**
 * Sanitizes the first name by removing unwanted characters and trimming whitespace.
 * @param string $first_name The input first name
 * @return string Sanitized first name
 */
function sanitizeFirstName($first_name) {
    return trim(preg_replace("/[^a-zA-Z'-]/", "", $first_name));
}

/**
 * Sanitizes the last name by removing unwanted characters and trimming whitespace.
 * @param string $last_name The input last name
 * @return string Sanitized last name
 */
function sanitizeLastName($last_name) {
    return trim(preg_replace("/[^a-zA-Z'-]/", "", $last_name));
}

/**
 * Sanitizes the email by trimming whitespace and ensuring a valid email format.
 * @param string $email The input email
 * @return string Sanitized email
 */
function sanitizeEmail($email) {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

/**
 * Sanitizes the contact number by keeping only numeric characters and trimming whitespace.
 * @param string $contact_number The input contact number
 * @return string Sanitized contact number
 */
function sanitizeContactNumber($contact_number) {
    return trim(preg_replace("/[^0-9]/", "", $contact_number));
}

/**
 * Sanitizes the password by trimming whitespace (no further alteration to preserve security).
 * @param string $password The input password
 * @return string Sanitized password
 */
function sanitizePassword($password) {
    return trim($password);
}

/**
 * Sanitizes the gender by converting to lowercase and trimming whitespace.
 * @param string $gender The input gender
 * @return string Sanitized gender
 */
function sanitizeGender($gender) {
    return strtolower(trim($gender));
}

/**
 * Sanitizes the company registration number by keeping only numeric characters and trimming whitespace.
 * @param string $registrationNo The input registration number
 * @return string Sanitized registration number
 */
function sanitizeCompanyRegNo($registrationNo) {
    return trim(preg_replace("/[^0-9]/", "", $registrationNo));
}

/**
 * Sanitizes the shop name by removing special characters and trimming whitespace.
 * @param string $shopName The input shop name
 * @return string Sanitized shop name
 */
function sanitizeShopName($shopName) {
    return trim(preg_replace("/[^A-Za-z0-9, -]/", "", $shopName));
}

/**
 * Sanitizes the shop description by trimming whitespace and removing malicious code.
 * @param string $description The input description
 * @return string Sanitized description
 */
function sanitizeShopDescription($description) {
    return trim(strip_tags($description));
}

/**
 * Sanitizes the category by keeping numeric characters and trimming whitespace.
 * @param string $category The input category
 * @return string Sanitized category
 */
function sanitizeCategory($category) {
    return trim(preg_replace("/[^0-9]/", "", $category));
}

/**
 * Sanitizes the product name by trimming whitespace and removing special characters.
 * @param string $productName The input product name
 * @return string Sanitized product name
 */
function sanitizeProductName($productName) {
    return trim(preg_replace("/[^A-Za-z0-9, -]/", "", $productName));
}

/**
 * Sanitizes the date of birth by ensuring it is in YYYY-MM-DD format and not in the future.
 * @param string $dob The input date of birth
 * @return string Sanitized date of birth or empty string if invalid
 */
function sanitizeDOB($dob) {
    $dob = trim($dob);
    // Check if the input matches YYYY-MM-DD format
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
        try {
            $date = new DateTime($dob);
            $now = new DateTime();
            // Ensure the date is not in the future
            if ($date <= $now) {
                return $dob;
            }
        } catch (Exception $e) {
            return ''; // Invalid date
        }
    }
    return ''; // Return empty string if invalid format
}

/**
 * Sanitizes the address by allowing alphanumeric characters, spaces, commas, and hyphens.
 * @param string $address The input address
 * @return string Sanitized address
 */
function sanitizeAddress($address) {
    return trim(preg_replace("/[^A-Za-z0-9, -]/", "", $address));
}
?>