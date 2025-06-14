# AI Tool Website

A comprehensive web application that provides various AI-powered tools through a user-friendly interface and robust API.

## 🚀 Overview

This project offers a suite of AI-powered functionalities including Grammar Checker, Image Upscaling, Text Summarizer, Background Removal, and Document Conversion. Built with Laravel, it features both user-facing tools and an administrative dashboard for monitoring usage statistics and system performance.

## ✨ Features

### User Dashboard
- **Tool Categories**: Organized into All, Image, Copywriting, and Document sections
- **Search Functionality**: Quick tool discovery
- **Available Tools**:
  - **Grammar Checker**: Enhance text accuracy and readability
  - **Image Upscaling**: Improve image resolution and quality
  - **Text Summarizer**: Generate concise summaries from long content
  - **Background Removal**: Remove backgrounds from images automatically
  - **Word to PDF Converter**: Convert Word documents to PDF format

### Admin Panel
- **Usage Statistics Dashboard**: Comprehensive analytics including:
  - Total usage metrics
  - Success/failure rates
  - Per-tool usage breakdown (pie chart visualization)
  - Daily usage trends (7-day line chart)
  - Recent usage logs (last 10 entries with timestamps and processing times)

## 💻 Tech Stack

- **Backend**: PHP 8.1+ with Laravel Framework
- **Frontend**: HTML5, CSS3, JavaScript
- **Database**: MySQL/PostgreSQL with Eloquent ORM
- **API Integration**: Multiple external AI service providers
- **Build Tools**: Node.js, npm for asset compilation

## ⚙️ Installation

### Prerequisites

Ensure you have the following installed:
- PHP >= 8.1
- Composer
- Node.js & npm
- Web server (Apache/Nginx)
- Database (MySQL/PostgreSQL)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/ai-tool-website.git
   cd ai-tool-website
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Environment configuration**
   ```bash
   cp .env.example .env
   ```
   Configure your database credentials and API keys in the `.env` file (see API Configuration section below).

4. **Generate application key**
   ```bash
   php artisan key:generate
   ```

5. **Database setup**
   ```bash
   php artisan migrate
   ```

6. **Install and build frontend assets**
   ```bash
   npm install
   npm run dev  # Development
   npm run build  # Production
   ```

7. **Web server configuration**
   Point your web server document root to the `public` directory.

## 🔑 API Configuration

Add the following environment variables to your `.env` file. Replace placeholder values with your actual API keys:

```env
# Google Gemini AI (via Google AI Studio)
GEMINI_API_URL="https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key="
GEMINI_API_KEY="your_gemini_api_key_here"

# Stability AI - Image Upscaling
UPSCALING_API_URL="https://api.stability.ai/v2beta/stable-image/upscale/fast"
UPSCALING_API_KEY="your_stability_ai_upscaling_api_key_here"

# Stability AI - Background Removal
REMOVEBG_API_URL="https://api.stability.ai/v2beta/stable-image/edit/remove-background"
REMOVEBG_API_KEY="your_stability_ai_removebg_api_key_here"

# CloudConvert - Document Conversion
CLOUDCONVERT_API_KEY="your_cloudconvert_api_key_here"
CLOUDCONVERT_API_BASE_URL="https://api.cloudconvert.com/v2"
```

> ⚠️ **Security Note**: Never commit actual API keys to version control. Keep your `.env` file in `.gitignore`.

## 🛠️ Adding New AI Tools

### 1. Backend Implementation

**Environment Configuration**
- Add API keys and URLs to your `.env` file
- Follow the existing naming conventions

**Controller/Service Development**
- Create or extend controller methods to handle the new tool
- Implement HTTP client calls to external AI services
- Handle response processing and error management
- Use Laravel's `Http` facade with `env()` for API configuration

**Route Definition**
- Register new API routes in `routes/api.php`
- Ensure proper middleware and validation

### 2. Frontend Integration

**User Interface**
- Create dedicated views/components for the new tool
- Design intuitive input and output interfaces
- Implement proper file upload handling if needed

**API Integration**
- Use JavaScript (Fetch/Axios) for asynchronous API calls
- Include CSRF token for security
- Implement loading states and error handling
- Provide user feedback and progress indicators

**Navigation**
- Add tool links to the main dashboard
- Update category filters if applicable

### 3. Database & Logging

**Schema Updates**
- Create migrations for tool-specific data if needed
- Update existing tables for new requirements

**Usage Tracking**
- Implement comprehensive logging for all tool interactions
- Record success/failure rates and processing times
- Ensure compatibility with admin dashboard statistics

### 4. Admin Panel Integration

- Register new tools in the system configuration
- Verify statistics integration and reporting
- Test dashboard visualizations with new tool data

## 🚀 Usage

### For End Users
1. Navigate to the application URL
2. Browse or search for desired AI tools
3. Select a tool and provide required input
4. Process and download results

### For Administrators
1. Access the admin panel with proper credentials
2. Monitor usage statistics and system performance
3. Review usage logs and identify trends
4. Manage system configuration as needed

## 🤝 Contributing

We welcome contributions from the community! Here's how you can help:

1. **Fork** the repository
2. **Create** a feature branch (`git checkout -b feature/AmazingFeature`)
3. **Commit** your changes (`git commit -m 'Add some AmazingFeature'`)
4. **Push** to the branch (`git push origin feature/AmazingFeature`)
5. **Open** a Pull Request

Please ensure your code follows our coding standards and includes appropriate tests.

## 📄 License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## 📞 Contact

**Project Maintainer**: [Farid Eka Aprilian]  
**Email**: faridaprilian214@gmail.com
**Project Repository**: https://github.com/fatidaprilian/AiTOOLS

---

⭐ If you find this project helpful, please consider giving it a star on GitHub!