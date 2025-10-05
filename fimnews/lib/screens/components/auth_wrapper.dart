import 'package:flutter/material.dart';
import '../../services/session_manager.dart';
import '../auth/loginScreen.dart';

class AuthWrapper extends StatefulWidget {
  final Widget child;
  final bool requireAuth;

  const AuthWrapper({
    super.key,
    required this.child,
    this.requireAuth = true,
  });

  @override
  State<AuthWrapper> createState() => _AuthWrapperState();
}

class _AuthWrapperState extends State<AuthWrapper> {
  bool _isLoading = true;
  bool _isAuthenticated = false;

  @override
  void initState() {
    super.initState();
    _checkAuthentication();
  }

  Future<void> _checkAuthentication() async {
    if (!widget.requireAuth) {
      setState(() {
        _isLoading = false;
        _isAuthenticated = true;
      });
      return;
    }

    try {
      final isValidSession = await SessionManager.validateAndRefreshSession();
      setState(() {
        _isAuthenticated = isValidSession;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isAuthenticated = false;
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(
        body: Center(
          child: CircularProgressIndicator(),
        ),
      );
    }

    if (!_isAuthenticated && widget.requireAuth) {
      return const LoginScreen();
    }

    return widget.child;
  }
}
