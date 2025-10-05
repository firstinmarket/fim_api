import 'package:flutter/material.dart';

class PrivacyPolicyScreen extends StatelessWidget {
  const PrivacyPolicyScreen({super.key});

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
          'Privacy Policy',
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
                      'Privacy Policy',
                      style: TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF5F8DFF),
                      ),
                    ),
                    const SizedBox(height: 16),
                    _buildSection(
                      'Information We Collect',
                      'We collect information you provide directly to us, such as when you create an account, update your profile, or interact with our services. This may include your name, email address, phone number, and preferences.',
                    ),
                    _buildSection(
                      'How We Use Your Information',
                      'We use the information we collect to provide, maintain, and improve our services, communicate with you, and personalize your experience. We may also use your information to send you updates and promotional materials.',
                    ),
                    _buildSection(
                      'Information Sharing',
                      'We do not sell, trade, or otherwise transfer your personal information to third parties without your consent, except as described in this privacy policy or as required by law.',
                    ),
                    _buildSection(
                      'Data Security',
                      'We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. However, no method of transmission over the internet is 100% secure.',
                    ),
                    _buildSection(
                      'Your Rights',
                      'You have the right to access, update, or delete your personal information. You can also opt out of certain communications from us. Contact us if you wish to exercise these rights.',
                    ),
                    _buildSection(
                      'Changes to This Policy',
                      'We may update this privacy policy from time to time. We will notify you of any changes by posting the new policy on this page and updating the effective date.',
                    ),
                    _buildSection(
                      'Contact Us',
                      'If you have any questions about this privacy policy, please contact us at support@fimtech.com or through our app support channels.',
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
