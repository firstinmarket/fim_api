import 'package:flutter/material.dart';

class BottomNavigation extends StatefulWidget {
  const BottomNavigation({super.key});

  @override
  State<BottomNavigation> createState() => _BottomNavigationState();
}

class _BottomNavigationState extends State<BottomNavigation> {
  final List<Map<String, dynamic>> tabs = [
    {'name': 'Post', 'icon': Icons.description, 'route': '/posts'},
    {'name': 'Unread', 'icon': Icons.mail_outline, 'route': '/unread'},
    {'name': 'Saved', 'icon': Icons.bookmark, 'route': '/saved'},
    {'name': 'Profile', 'icon': Icons.person, 'route': '/profile'},
  ];

  @override
  Widget build(BuildContext context) {
    final currentRoute = ModalRoute.of(context)?.settings.name;

    return Container(
      decoration: const BoxDecoration(
        color: Color(0xFF181818),
        borderRadius: BorderRadius.only(
          topLeft: Radius.circular(16),
          topRight: Radius.circular(16),
        ),
      ),
      padding: const EdgeInsets.symmetric(vertical: 10),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: tabs.map((tab) {
          final isActive = currentRoute == tab['route'];
          return GestureDetector(
            onTap: () => Navigator.pushNamed(context, tab['route']),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(
                  tab['icon'],
                  size: 22,
                  color: Colors.white,
                ),
                const SizedBox(height: 2),
                Text(
                  tab['name'],
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 13,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
          );
        }).toList(),
      ),
    );
  }
}