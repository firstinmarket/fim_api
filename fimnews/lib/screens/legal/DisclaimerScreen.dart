import 'package:flutter/material.dart';

class DisclaimerScreen extends StatelessWidget {
  const DisclaimerScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0F0F0F),
      appBar: AppBar(
        backgroundColor: const Color(0xFF0F0F0F),
        elevation: 0,
        centerTitle: true,
        leading: IconButton(
          onPressed: () => Navigator.pop(context),
          icon: const Icon(
            Icons.arrow_back,
            color: Color(0xFF5F8DFF),
          ),
        ),
        title: const Text(
          'Disclaimer',
          style: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.w700,
            color: Color(0xFF5F8DFF),
          ),
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: const Color(0xFF232A3B),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                      color: const Color(0xFF5F8DFF).withOpacity(0.3)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Disclaimer',
                      style: TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF5F8DFF),
                      ),
                    ),
                    const SizedBox(height: 16),
                    _buildSection(
                      'General Information',
                      'The information contained in this application is for general information purposes only. While we strive to keep the information up to date and correct, we make no representations or warranties of any kind about the completeness, accuracy, reliability, or availability of the information.',
                    ),
                    _buildSection(
                      'No Professional Advice',
                      'The content provided in this application is not intended as professional advice and should not be relied upon for making decisions. We recommend consulting with qualified professionals for specific advice related to your circumstances.',
                    ),
                    _buildSection(
                      'Use at Your Own Risk',
                      'Your use of this application and reliance on any information provided is solely at your own risk. We will not be liable for any loss or damage including, without limitation, indirect or consequential loss or damage arising from the use of this application.',
                    ),
                    _buildSection(
                      'Third-Party Content',
                      'This application may contain links to third-party websites or content. We have no control over the nature, content, and availability of those sites. The inclusion of any links does not necessarily imply a recommendation or endorsement.',
                    ),
                    _buildSection(
                      'Accuracy of Information',
                      'We make every effort to ensure that the information in this application is accurate and up to date. However, we cannot guarantee that all information is error-free or complete. Information may be changed or updated without notice.',
                    ),
                    _buildSection(
                      'Limitation of Liability',
                      'To the fullest extent permitted by law, we exclude all liability for any direct, indirect, incidental, special, consequential, or punitive damages arising from your use of this application or any information contained within it.',
                    ),
                    _buildSection(
                      'Changes to Disclaimer',
                      'We reserve the right to modify this disclaimer at any time. Changes will be effective immediately upon posting. Your continued use of the application after changes constitutes acceptance of the updated disclaimer.',
                    ),
                    _buildSection(
                      'Contact Information',
                      'If you have any questions about this disclaimer, please contact us at support@fimtech.com or through our app support channels.',
                    ),
                    const SizedBox(height: 20),
                    const Text(
                      'Last updated: October 2025',
                      style: TextStyle(
                        color: Color(0xFFAAAAAA),
                        fontSize: 12,
                        fontStyle: FontStyle.italic,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSection(String title, String content) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: const TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w600,
              color: Color(0xFF5F8DFF),
            ),
          ),
          const SizedBox(height: 8),
          Text(
            content,
            style: const TextStyle(
              fontSize: 14,
              color: Color(0xFFEEEEEE),
              height: 1.6,
            ),
          ),
        ],
      ),
    );
  }
}
