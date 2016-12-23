# Upgrade from 1.2 to 2.0

# REST Controller

 * The deprecated methods are removed from the RestController:

   Removed                  | Use instead
   ------------------------ | -------------------------------
   `putDocumentAction()`    | `updateDocumentAction()`
   `deleteDocumentAction()` | `updateDocumentAction()`
   `performSecurityCheck()` | `$this->accessChecker->check()`
