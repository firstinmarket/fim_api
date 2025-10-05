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
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: isActive
                    ? const Color(0xFF5F8DFF).withOpacity(0.2)
                    : Colors.transparent,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    tab['icon'],
                    size: isActive ? 26 : 22,
                    color: isActive ? const Color(0xFF5F8DFF) : Colors.white70,
                  ),
                  const SizedBox(height: 2),
                  Text(
                    tab['name'],
                    style: TextStyle(
                      color:
                          isActive ? const Color(0xFF5F8DFF) : Colors.white70,
                      fontSize: isActive ? 14 : 13,
                      fontWeight: isActive ? FontWeight.w700 : FontWeight.w500,
                    ),
                  ),
                  // Active indicator dot
                  if (isActive) ...[
                    const SizedBox(height: 2),
                    Container(
                      width: 4,
                      height: 4,
                      decoration: const BoxDecoration(
                        color: Color(0xFF5F8DFF),
                        shape: BoxShape.circle,
                      ),
                    ),
                  ],
                ],
              ),
            ),
          );
        }).toList(),
      ),
    );
  }
}
