import React from 'react';

/**
 * CommentBody Component
 * Renders user-provided strings while allowing <b> and <i> tags.
 */
const CommentBody = ({ htmlContent }) => {
  // It is best practice to sanitize this string before rendering
  // if it comes from an untrusted source.
  return (
    <div 
      className="comment-body"
      dangerouslySetInnerHTML={{ __html: htmlContent }} 
    />
  );
};

// Usage Example:
// <CommentBody htmlContent="This is a <b>bold</b> and <i>italic</i> comment." />

export default CommentBody;