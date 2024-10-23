# Course Preview Module for Magento 2

## Overview

The **Course Preview** module is a custom Magento 2 module that allows you to upload video previews of courses and display them on the product page. The module provides an HTML5 video player with a playlist of videos for customers to view before purchasing the course. The videos are uploaded through the admin product edit page and can be displayed on the frontend.

## Features

- Upload video files or provide video URLs for course previews on the product edit page in the admin panel.
- Display an HTML5 video player on the product details page (PDP) to show course previews.
- Provide a playlist of multiple videos.
- Responsive video player and playlist section.
- Opens video player in a popup modal when the customer clicks the "Watch Course Review" button.
- Smooth video transitions without reloading the page.
- Supports video and image file uploads.
- Enable/disable the module via store configuration in the admin panel.

## Screenshots

### Admin View
![Admin View](https://github.com/wajahatbashir/wajahatbashir/blob/main/images/admin-product-custom-fields-form.jpg)

### Frontend View
![Frontend View](https://github.com/wajahatbashir/wajahatbashir/blob/main/images/Product-details-page-preview.jpg)

## Installation

1. **Copy the files**: Download and extract the module files to your Magento installation under the `app/code/CI/CoursePreview` directory.

2. **Enable the module**: Run the following Magento CLI commands:

```bash
php bin/magento module:enable CI_CoursePreview
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:clean
```

3. **Module Configuration**:  
   To enable or disable the module, go to **Stores** > **Configuration** > **General** > **Course Preview Configuration** and toggle the setting for the Course Preview Module.

## How to Use

### Admin Configuration

1. Go to the **Magento Admin Panel**.
2. Navigate to **Catalog** > **Products**.
3. Edit any product.
4. You will find a section called **Course Previews** where you can upload video files or provide video URLs, along with titles and descriptions for each video.
5. You can add multiple course preview videos using the "Add Course Preview" button.

### Frontend Display

1. On the **Product Detail Page (PDP)**, you will see a button labeled **"Watch Course Review"**.
2. Clicking this button will open a popup with the video player and the list of preview videos.
3. Customers can select any video from the playlist to watch in the video player.

## Technical Details

### File Upload

- The module uses Magento's `fileUploader` component for handling video and image uploads.
- Videos are stored under the `/pub/media/course_videos/` directory.

### Video Player

- The video player on the frontend uses the native HTML5 `<video>` element.
- The playlist is dynamically rendered, and the video player updates seamlessly without page reloads.

### Controller

- The module includes a custom admin controller for handling video and image uploads.
- The controller processes the files and stores them in the appropriate media directory.

### Popup Modal

- The video player is displayed in a popup modal triggered by a button.
- The modal can only be closed by clicking the close button (`×`), ensuring that the video viewing experience is not interrupted by accidental clicks outside the modal.

## System Configuration

- Navigate to **Stores** > **Configuration** > **General** > **Course Preview Configuration**.
- Enable or disable the Course Preview module from the store configuration settings.

## For Developers

This module is open source and encourages contributions from other developers. Developers can use this module as a foundation for adding custom fields on the product add/edit page as per their requirements. Feel free to enhance the current functionality or extend it with new features such as:

- Adding more input fields or custom configurations on the product page.
- Supporting additional file formats.
- Enhancing the admin interface for bulk video uploads.
- Adding captions, playback rates, or other video player enhancements.

### Developer Notes

- You can use this module as an example of how to add custom fields on the product page in Magento 2.
- The video and image upload functionality is based on the `fileUploader` component, which can be extended for other types of uploads.
- Developers are encouraged to participate in enhancing this module and share improvements with the community.

## Future Improvements

- Add support for other video formats like `.webm`.
- Implement a fallback for browsers that do not support HTML5 video playback.
- Enhance the admin UI for bulk video uploads.
- Add more control options in the video player (e.g., captions, playback rate).

## License

This module is open-source and free to use under the MIT License.