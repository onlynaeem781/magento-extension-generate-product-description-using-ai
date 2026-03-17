## Mnaeem_AiProductDescription (Magento 2)

AI-based Product Description Generator for Magento 2.4+ that integrates with OpenAI to generate **SEO-optimized** product descriptions from the **Admin product edit** page.

## Features

- **Admin configuration** under **Stores → Configuration → Mnaeem → AI Product Description**
- **Generate AI Description** button on the Catalog Product edit page
- Sends product name, short description, attributes, category, and keywords to the AI API
- Fills the product **Description** field with the generated content
- Configurable prompt template and API settings (model, max tokens, temperature)

## Requirements

- Magento 2.4+
- PHP 7.4+ or 8.1+
- Valid OpenAI API key

## Module path

- `app/code/Mnaeem/AiProductDescription`

## Installation

1. **Place the module** into your Magento installation:

   ```bash
   ls -la app/code/Mnaeem/AiProductDescription
   ```

2. **Enable the module** and run setup:

   ```bash
   php bin/magento module:enable Mnaeem_AiProductDescription
   php bin/magento setup:upgrade
   php bin/magento cache:flush
   ```

3. **Configure** at **Stores → Configuration → Mnaeem → AI Product Description**:

   - Enable the module
   - Set **API Provider** to OpenAI
   - Enter your **API Key**
   - Set **AI Model** (e.g. `gpt-4o-mini`, `gpt-4o`)
   - Adjust **Max Tokens** and **Temperature** if needed
   - Optionally edit the **Default Prompt Template**

4. **Permissions**: Ensure the admin user has **Catalog → Products → Generate AI Description** and **Stores → Configuration → AI Product Description Config** (if editing config).

## Usage (Admin)

1. Go to **Catalog → Products** and edit a product.
2. Click the **Generate AI Description** button.
3. Wait for the request to complete. The generated text is inserted into the **Description** field.
4. Edit the description if needed and **Save** the product.

## API Integration

- **Endpoint**: `https://api.openai.com/v1/chat/completions`
- **Auth**: `Authorization: Bearer <API_KEY>`
- **Timeouts**: connect timeout 10s, request timeout 60s
- **Errors**: OpenAI errors are validated and surfaced in Admin (and logged)

## Example AI Prompt (sent to the API)

The default prompt template uses placeholders that are replaced with product data. Example of the **text actually sent to the API** (with sample values):

```text
Generate an SEO-optimized product description for an e-commerce store. Use the following product information:

Product Name: Wireless Bluetooth Headphones
Short Description: High-quality sound with 20-hour battery life.
Category: Electronics, Audio, Headphones
Attributes: color: Black
brand: SoundMax
material: Plastic, Metal
Keywords: wireless headphones, bluetooth, audio, music

Requirements: Write a compelling, professional product description that is SEO-friendly, includes relevant keywords naturally, and is suitable for the product page. Output only the description text, no meta or labels.
```

The API response (generated description) is then placed into the product **Description** field.

## Placeholders in Prompt Template

You can customize the prompt in **Stores → Configuration → AI Product Description → Prompt Settings** using:

- `{{product_name}}`
- `{{short_description}}`
- `{{attributes}}`
- `{{category}}`
- `{{keywords}}`

## Troubleshooting

### Admin button not visible

- Ensure module is enabled and caches are flushed:

```bash
php bin/magento module:status Mnaeem_AiProductDescription
php bin/magento cache:flush
```

### Invalid Form Key

- Ensure the admin session is valid and you are not posting from a cached page. The module reads the existing hidden `form_key` input from the product edit page for AJAX requests.

## "Quota exceeded" or "check your plan and billing" errors

If you see **"You exceeded your current quota, please check your plan and billing details"** (or similar) even though your OpenAI account shows credits:

- **Confirm the API key** in Stores → Configuration → AI Product Description is from the same OpenAI account that has the credits and paid plan.
- **Check billing**: [platform.openai.com/account/billing](https://platform.openai.com/account/billing) — ensure payment method is added and there are no limits or restrictions.
- **Usage limits**: Some accounts have per-minute or daily caps; wait a moment or check Usage in the OpenAI dashboard.
- **Key type**: Use an API key from the account that has the subscription/credits (not an old or revoked key).

The module surfaces OpenAI’s error message; resolving it is done in your OpenAI account and billing settings.

## Security

- Avoid committing API keys to version control. Prefer environment-specific values and encrypted configuration in production.
- Admin actions are protected by ACL (`Mnaeem_AiProductDescription::generate`).
- API responses are validated; invalid or error responses are handled and not written blindly to the product.

## Code Structure (summary)

- **Api/ProductDescriptionGeneratorInterface.php** – Service contract for description generation
- **Model/ProductDescriptionGenerator.php** – Builds prompt and calls API client
- **Model/Http/OpenAiClient.php** – HTTP client for OpenAI API
- **Model/Config.php** – Reads module configuration
- **Controller/Adminhtml/Product/GenerateDescription.php** – AJAX endpoint used by the button
- **Block/Adminhtml/Product/Edit/GenerateDescriptionButton.php** – Provides URL and IDs for the button
- **view/adminhtml/** – Layout, template, and JS for the “Generate AI Description” button

## Compatibility

- Developed for **Magento 2.4+**.
- Follows Magento 2 coding standards, PSR-12, and uses dependency injection and service contracts where appropriate.
